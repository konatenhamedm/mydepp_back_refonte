import os

admin_dir = "/Volumes/konate/ANVOH/mydepp_front_next/app/(dashboard)/admin"
non_paginated_files = []

for root, dirs, files in os.walk(admin_dir):
    for file in files:
        if file == "page.tsx":
            path = os.path.join(root, file)
            with open(path, "r", encoding="utf-8") as f:
                content = f.read()
            
            # Check if it has apiFetch
            has_api_fetch = "apiFetch" in content
            # Check if it has with_pagination=true
            has_server_pagination = "with_pagination=true" in content
            # Check if it does client-side slicing
            has_client_slice = ".slice(" in content or "slice(" in content

            if has_api_fetch and (not has_server_pagination or has_client_slice):
                relative_path = os.path.relpath(path, admin_dir)
                non_paginated_files.append((relative_path, has_server_pagination, has_client_slice))

print(f"Found {len(non_paginated_files)} non-paginated or partially paginated admin page.tsx files:")
for path, has_server, has_slice in non_paginated_files:
    status = []
    if not has_server:
        status.append("Missing with_pagination=true")
    if has_slice:
        status.append("Uses client-side .slice()")
    print(f" - {path} ({', '.join(status)})")
