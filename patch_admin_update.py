import re

file_path = "src/Controller/Apis/ApiProfessionnelOldController.php"
with open(file_path, "r") as f:
    content = f.read()

endpoint = """
    #[Route('/update-lieu-naissance', name: 'api_professionnel_old_update_lieu', methods: ['POST'])]
    #[OA\Tag(name: 'professionnel')]
    public function updateLieuNaissance(Request $request, ProfessionnelRepository $professionnelRepository): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            $id = $data['id'] ?? null;
            $lieu = $data['lieuNaissance'] ?? null;

            if (!$id || !$lieu) {
                return $this->json(['statut' => 0, 'message' => 'ID et lieuNaissance requis.'], 400);
            }

            $professionnel = $professionnelRepository->find($id);
            if (!$professionnel) {
                return $this->json(['statut' => 0, 'message' => 'Professionnel introuvable.'], 404);
            }

            $professionnel->setLieuNaissance($lieu);
            $this->em->persist($professionnel);
            $this->em->flush();

            return $this->json(['statut' => 1, 'message' => 'Lieu de naissance mis à jour avec succès.']);
        } catch (\Exception $e) {
            return $this->json(['statut' => 0, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
"""

if "/update-lieu-naissance" not in content:
    # Insert before the last closing brace
    content = content.rsplit("}", 1)[0] + endpoint + "\n}"
    with open(file_path, "w") as f:
        f.write(content)
    print("Endpoint added")
else:
    print("Endpoint already exists")
