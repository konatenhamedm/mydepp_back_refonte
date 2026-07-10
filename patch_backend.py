import re

with open('src/Controller/Apis/ApiProfessionnelOldController.php', 'r') as f:
    content = f.read()

old_data = """                'renewalYear'           => $renewalYear,
                'photo'                 => $photoFile ? $this->getFichierUrl($photoFile, $request) : null,
            ];"""

new_data = """                'renewalYear'           => $renewalYear,
                'photo'                 => $photoFile ? $this->getFichierUrl($photoFile, $request) : null,
                'piece'                 => $professionnel->getCni() ? $this->getFichierUrl($professionnel->getCni(), $request) : null,
            ];"""

if old_data in content:
    content = content.replace(old_data, new_data)
    with open('src/Controller/Apis/ApiProfessionnelOldController.php', 'w') as f:
        f.write(content)
    print("Backend patched")
else:
    print("Failed to patch backend")
