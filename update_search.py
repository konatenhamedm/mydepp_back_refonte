import re

file_path = "src/Controller/Apis/ApiProfessionnelOldController.php"
with open(file_path, "r") as f:
    content = f.read()

old_lieu = "$lieuNaissance = !empty($lieuParts) ? implode('/', array_slice(array_values($lieuParts), 0, 2)) . ' (Côte d\\'Ivoire)' : 'Côte d\\'Ivoire';"
new_lieu = "$lieuNaissance = $professionnel->getLieuNaissance() ?: (!empty($lieuParts) ? implode('/', array_slice(array_values($lieuParts), 0, 2)) . ' (Côte d\\'Ivoire)' : 'Côte d\\'Ivoire');"

if old_lieu in content:
    content = content.replace(old_lieu, new_lieu)
    with open(file_path, "w") as f:
        f.write(content)
    print("Updated searchForAttestation in ApiProfessionnelOldController.php")
else:
    print("Could not find insertion point in ApiProfessionnelOldController.php")
