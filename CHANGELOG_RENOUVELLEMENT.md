# Résumé des modifications : Logique de Renouvellement

## Objectif
Permettre le calcul dynamique du montant dû pour le renouvellement des professionnels de santé, en se basant sur l'année extraite de leur code d'identification (pour les codes commençant par `MS` suivis de l'année, ex: `MS2022ADENT2987.0002`).

## Ce qui a été fait

Nous avons mis à jour la logique de calcul de la variable `$yearDue` (nombre d'années d'arriérés) dans les différents contrôleurs et services responsables de la gestion des paiements et du statut des professionnels. 

### Nouvelle règle de calcul
1. Le système vérifie si le code du professionnel correspond au pattern `MS` + `Année` (ex: `MS2022`).
2. Si c'est le cas, le nombre d'années dues est calculé en soustrayant cette année à l'année courante : `Année Actuelle (ex: 2026) - Année du Code (ex: 2022) = 4 ans`.
3. Si le code ne correspond pas à ce pattern, le système retombe sur l'ancienne logique (différence entre l'année courante et la date d'expiration de validation du professionnel).

### Fichiers modifiés
*   `src/Controller/Apis/ApiPaiementController.php` (Méthode `status`) : 
    Mise à jour pour que l'API de vérification de statut renvoie le bon `yearDue` au frontend.
*   `src/Controller/PaymentProController.php` (Méthode `status`) : 
    Mise à jour similaire pour ce second contrôleur de statut.
*   `src/Service/PaiementProService.php` (Méthode `traiterPaiementRenouvellement`) : 
    Mise à jour de la logique lors de l'initiation du paiement (POST) pour s'assurer que le montant total calculé respecte bien cette règle.
*   `src/Service/PaiementService.php` (Méthode `traiterPaiementRenouvellement`) : 
    Alignement de la logique pour le service de paiement standard.

## Impact
Les professionnels avec un ancien code (ex: `MS2022...`) verront automatiquement leur dette de renouvellement ajustée à la réalité de leur ancienneté lorsqu'ils accèderont à leur interface de renouvellement.


for table in $(php bin/console dbal:run-sql "SELECT table_name FROM information_schema.columns WHERE column_name = 'created_by_id' AND table_schema = DATABASE();" | grep -v "TABLE_NAME" | grep -v "-" | awk '{print $1}'); do
  if [ -n "$table" ]; then
    echo "Nettoyage de $table..."
    php bin/console dbal:run-sql "UPDATE $table SET created_by_id = NULL WHERE created_by_id NOT IN (SELECT id FROM utilisateur);" > /dev/null 2>&1
    php bin/console dbal:run-sql "UPDATE $table SET updated_by_id = NULL WHERE updated_by_id NOT IN (SELECT id FROM utilisateur);" > /dev/null 2>&1
  fi
done