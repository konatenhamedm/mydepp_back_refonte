import os
import re

admin_dir = "/Volumes/konate/ANVOH/mydepp_front_next/app/(dashboard)/admin"

# List of pages to migrate
simple_pages = [
    "district/page.tsx",
    "direction/page.tsx",
    "destinataire/page.tsx",
    "commune/page.tsx",
    "code_generateur/page.tsx",
    "administrateur/page.tsx",
    "admin_document/page.tsx",
    "ordre/page.tsx",
    "niveau_intervention/page.tsx",
    "lieu_diplome/page.tsx",
    "region/page.tsx",
    "type_profession/page.tsx",
    "ville/page.tsx",
    "racine_sequence/page.tsx",
    "situation_professionnelle/page.tsx",
    "status_pro/page.tsx",
    "historique_paiement/page.tsx",
    "type_personne/page.tsx",
    "historique_paiement_etablissement/page.tsx",
    "type_document/page.tsx",
    "type_diplome/page.tsx",
    "utilisateur_externe/page.tsx"
]

migrated_count = 0

for page_rel in simple_pages:
    path = os.path.join(admin_dir, page_rel)
    if not os.path.exists(path):
        print(f"File not found: {path}")
        continue
    
    with open(path, "r", encoding="utf-8") as f:
        content = f.read()
    
    # Ensure it's a simple CRUD list and not already paginated
    if "const TABS =" in content or "with_pagination=true" in content:
        print(f"Skipping {page_rel} (contains tabs or already paginated)")
        continue

    print(f"Migrating {page_rel}...")

    # 1. Update React import to include useCallback
    content = re.sub(
        r'import React,\s*\{\s*useEffect,\s*useState\s*\}\s*from\s*"react";',
        'import React, { useEffect, useState, useCallback } from "react";',
        content
    )
    content = re.sub(
        r'import React,\s*\{\s*useState,\s*useEffect\s*\}\s*from\s*"react";',
        'import React, { useEffect, useState, useCallback } from "react";',
        content
    )

    # 2. Add totalItems state
    if "totalItems" not in content:
        content = re.sub(
            r'const\s*\[\s*data\s*,\s*setData\s*\]\s*=\s*useState<any\[\]>\(\[\]\);',
            'const [data, setData] = useState<any[]>([]);\n    const [totalItems, setTotalItems] = useState(0);',
            content
        )

    # 3. Find the endpoint inside refreshData
    endpoint_match = re.search(r'apiFetch\(\s*["\']([^"\']+)["\']\s*\)', content)
    if not endpoint_match:
        print(f"Could not find apiFetch endpoint in {page_rel}")
        continue
    endpoint = endpoint_match.group(1)

    # 4. Replace refreshData function
    refresh_pattern = r'const\s+refreshData\s*=\s*\(\)\s*=>\s*\{[^}]+apiFetch\("[^"]+"\)[^}]+};'
    # Since refreshData can span multiple lines, let's use a more flexible regex:
    refresh_pattern_multi = r'const\s+refreshData\s*=\s*\(\)\s*=>\s*\{[\s\S]+?apiFetch\s*\(\s*["\']' + re.escape(endpoint) + r'["\']\s*\)[\s\S]+?\};'
    
    new_refresh = f"""const refreshData = useCallback(() => {{
        setIsLoading(true);
        apiFetch(`{endpoint}?with_pagination=true&page=${{currentPage}}&limit=${{itemsPerPage}}&search=${{searchTerm}}`)
            .then((res) => {{
                const raw = Array.isArray(res?.data) ? res.data : res?.data?.data ?? [];
                setData(raw);
                if (res?.pagination) {{
                    setTotalItems(res.pagination.totalItems ?? 0);
                }} else {{
                    setTotalItems(raw.length);
                }}
                setIsLoading(false);
            }})
            .catch(() => setIsLoading(false));
    }}, [currentPage, searchTerm, itemsPerPage]);"""

    content = re.sub(refresh_pattern_multi, new_refresh, content)

    # 5. Replace filteredData and currentitems slicing
    # Find filteredData assignment
    filtered_pattern = r'const\s+filteredData\s*=\s*Array\.isArray\(data\)[\s\S]+?:\s*\[\]\s*;'
    content = re.sub(filtered_pattern, '', content)

    # Replace currentitems
    current_items_pattern = r'const\s+currentitems\s*=\s*filteredData\.slice\([\s\S]+?\);'
    content = re.sub(current_items_pattern, 'const currentitems = data;', content)

    # Replace filteredData.length with totalItems in PageHeader, SearchBar, Pagination, etc.
    content = re.sub(r'filteredData\.length', 'totalItems', content)

    # Replace useEffect dependency
    content = re.sub(r'useEffect\(\(\)\s*=>\s*\{\s*refreshData\(\);\s*\}\s*,\s*\[\]\);', 'useEffect(() => { refreshData(); }, [refreshData]);', content)

    with open(path, "w", encoding="utf-8") as f:
        f.write(content)
    
    print(f"Successfully migrated {page_rel}!")
    migrated_count += 1

print(f"\nMigration complete. Migrated {migrated_count} simple CRUD list views!")
