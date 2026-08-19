# Modifications — Renouvellement professionnel

Correctif du bug bloquant l'affichage/calcul du renouvellement d'abonnement des professionnels.

## Problème identifié

Le calcul de la dette de renouvellement (`GET /api/paiement2/status/renouvellement/{userId}`) se basait
sur un `preg_match` cherchant une année à 4 chiffres isolée dans le `code` du professionnel
(ex: `MS2022...`). Or le format réel des codes générés (`Utils::numeroGeneration()`) colle l'année
directement à d'autres chiffres, sans séparateur (ex: `DEF2026000579.0001`) — le regex ne matchait
donc **jamais**. Résultat : le système ne considérait jamais qu'un professionnel devait renouveler,
peu importe depuis combien de temps son abonnement était expiré, et l'interface de renouvellement
n'affichait jamais le sélecteur d'années à régulariser.

De plus, `dateValidation` était fixée à `new DateTime()` (aujourd'hui) à la création/validation du
compte à plusieurs endroits, alors que le calcul de facturation réel
(`PaiementProService::traiterPaiementRenouvellement`) et la mise à jour post-paiement
(`PaiementProService::finaliserRenouvellement`) traitent déjà `dateValidation` comme étant
**directement la date d'expiration** (ex: `now + 1 an` après un renouvellement complet).

## Solution

Uniformiser la convention `dateValidation = date d'expiration de l'abonnement` partout, et faire en
sorte que l'affichage (`status()`) utilise la même source de vérité que la facturation
(`traiterPaiementRenouvellement()`), au lieu de parser le `code`.

## Fichiers modifiés

### 1. `src/Controller/PaymentProController.php` — méthode `status()`
Remplacement du calcul basé sur le regex année-dans-le-code par un calcul basé sur
`Professionnel::getDateValidation()`, avec la même logique que
`PaiementProService::traiterPaiementRenouvellement()` (expiration = `dateValidation`, `yearDue` =
différence d'années avec aujourd'hui, minimum 1 an si expiré).

### 2. `src/Service/PaiementService.php` — méthode `updateProfessionnel()`
`setDateValidation(new DateTime())` → `setDateValidation((new DateTime())->modify('+1 year'))`
(création du professionnel juste après un paiement initial réussi — circuit ONMCI legacy).

### 3. `src/Service/PaiementBusinessLogicService.php` — méthode `updateProfessionnel()`
Même correctif que ci-dessus (circuit de paiement plus récent / Hub2).

### 4. `src/Controller/Apis/ApiProfessionnelController.php` — méthode `active()` (validation admin)
Même correctif : quand un admin valide manuellement un compte (workflow `attente` → `validation`),
la date d'expiration initiale est maintenant fixée à un an à partir d'aujourd'hui, au lieu
d'aujourd'hui même (ce qui aurait fait apparaître le compte comme "expiré" dès le lendemain avec
le nouveau calcul de `status()`).

## Non modifié (hors périmètre de cette demande)

- La branche ÉTABLISSEMENT de `status()` a le même défaut (parsing du code), mais n'a pas été
  touchée — la demande portait spécifiquement sur les professionnels.
- Le fait que `finaliserRenouvellement()` ne régénère pas le `code` du professionnel après paiement
  n'est plus un problème puisque `status()` ne dépend plus du `code`.

---

# Modifications — Création de compte via numéro d'inscription

## Problème identifié

`POST /api/user/api/create-new-user-with-code` ([ApiUserController.php:1021](src/Controller/Apis/ApiUserController.php#L1021))
ne vérifiait pas si l'email était déjà utilisé (alors que `User.email` a une contrainte unique en
base, `UNIQ_IDENTIFIER_EMAIL`) et n'avait aucun `try/catch`. Un professionnel retentant la création
de compte avec un email déjà utilisé provoquait un crash brut (exception Doctrine non interceptée,
erreur 500 sans message clair).

## Solution

Ajout d'un `try/catch` autour de la mise à jour du professionnel, la création du `User` et l'envoi
du mail : `UniqueConstraintViolationException` renvoie désormais un message clair ("Cet email est
déjà utilisé par un autre compte."), et toute autre exception renvoie un message générique au lieu
de planter silencieusement.

## Fichier modifié

- `src/Controller/Apis/ApiUserController.php` — méthode `createUserWithCode()`

## Non corrigé (hors périmètre de cette demande)

- Aucune contrainte d'unicité n'existe sur la relation `personne` (User → Professionnel) : rien
  n'empêche encore la création de plusieurs comptes différents pour le même numéro d'inscription
  avec des emails différents. Le try/catch évite le crash, mais ne résout pas ce problème de fond.

## Vérifications effectuées

- `php -l` sur les 4 fichiers modifiés : aucune erreur de syntaxe.
- Simulation isolée de la nouvelle logique de date (`dateValidation` + 1 an à la création, calcul
  de `yearDue` en cas d'expiration) : résultats cohérents.
- **Non testé en conditions réelles** : le backend local n'a pas de `.env` configuré (pas de
  `DATABASE_URL`), donc aucun test d'intégration/requête HTTP réelle n'a pu être exécuté dans cette
  session.

## À vérifier avant mise en prod

- Rejouer un scénario complet (inscription payante → paiement MoMo → `status()` → renouvellement →
  nouveau `status()`) sur un environnement avec base de données pour confirmer le comportement de
  bout en bout.
- Vérifier les professionnels déjà existants en base dont `dateValidation` a été fixée par l'ancien
  code (sans le `+1 an`) : leur date d'expiration affichée sera incorrecte tant qu'ils n'auront pas
  renouvelé au moins une fois (elle sera basée sur leur ancienne `dateValidation`, potentiellement
  déjà passée). Une migration de données ponctuelle peut être nécessaire pour corriger rétroactivement
  ces dates.
