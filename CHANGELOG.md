# Changelog — mydepp_back_refonte

> Récapitulatif de toutes les modifications apportées au backend Symfony.

---

## [Session 1-2] Corrections de bugs critiques

### `src/Controller/Apis/ApiProfessionnelController.php`

#### Bug : `Call to a member function getProfession() on null` (HTTP 500)

**Contexte :** Certains utilisateurs de type `PROFESSIONNEL` en base de données n'ont pas d'entité `personne` liée. Les closures dans `array_map` de `index()` et `getProfessionnelByEtat()` appelaient `->getProfession()` sans vérifier si `$personne` était null, provoquant un crash 500.

**Fix appliqué dans les méthodes `index()` et `getProfessionnelByEtat()` :**
```php
$formattedProfessionnels = array_map(function ($professionnel) use ($professionRepository) {
    $personne = $professionnel->getPersonne();
    if (!$personne) return null; // ← AJOUTÉ : guard contre null
    // ...
}, $professionnels);
$response = $this->responseData(
    array_values(array_filter($formattedProfessionnels)), // ← filtre les nulls
    'group_pro',
    ...
);
```

---

#### Bug : `ValidationWorkflow::setCreatedAtValue()` — TypeError (HTTP 500)

**Contexte :** La méthode `setCreatedAtValue()` de l'entité `ValidationWorkflow` attend un `?DateTimeImmutable`, mais du code lui passait un `DateTime` (mutable), causant un `TypeError`.

**Localisation :** `active()` — ligne ~746.

**Fix :** Suppression des appels explicites aux setters de date. Doctrine gère les timestamps automatiquement via les lifecycle callbacks (`#[ORM\PrePersist]` / `#[ORM\PreUpdate]`).

```php
// AVANT (erreur)
$validationWorkflow->setCreatedAtValue(new DateTime('now'));
$validationWorkflow->setUpdatedAt(new DateTime('now'));

// APRÈS (corrigé) — les dates sont gérées par les lifecycle callbacks
$validationWorkflow->setCreatedBy($user);
$validationWorkflow->setUpdatedBy($user);
```

**Règle à respecter :** Ne jamais passer de date manuellement à `setCreatedAtValue()` ou `setUpdatedAt()` lors de la création ou modification d'une entité. Laisser ces champs à `null` pour que le système les remplisse automatiquement.

---

### `src/Controller/Apis/ApiUserController.php`

#### Bug : Noms des instructeurs affichés "undefined undefined" dans le frontend

**Contexte :** L'entité `User` n'a pas de champs `nom`/`prenoms` directs — ces données sont sur l'entité liée `personne` (Entite). Le groupe de sérialisation `group_user` n'exposait pas correctement ces champs, ce qui retournait `null` pour `nom` et `prenoms` au frontend.

**Fix dans `indexInstructeur()` et `indexInstructeurEtab()` :** Remplacement de la sérialisation automatique par un tableau formaté manuellement, utilisant l'opérateur null-safe `?->` pour éviter les erreurs si `personne` est null :

```php
$formatted = array_map(fn($u) => [
    'id'      => $u->getId(),
    'email'   => $u->getEmail(),
    'nom'     => $u->getPersonne()?->getNom(),
    'prenoms' => $u->getPersonne()?->getPrenoms(),
], $users);
$response = $this->responseData($formatted, 'group_user', ['Content-Type' => 'application/json']);
```

---

## Résumé des fichiers modifiés

| Fichier | Type de modification |
|---|---|
| `src/Controller/Apis/ApiProfessionnelController.php` | Correction null guard + fix TypeError DateTime |
| `src/Controller/Apis/ApiUserController.php` | Fix formatage retour instructeurs |

---

## Bonnes pratiques identifiées

- **Toujours garder un null guard** sur `$professionnel->getPersonne()` dans les `array_map` du contrôleur professionnel (certains utilisateurs PROFESSIONNEL n'ont pas d'entité personne liée).
- **Ne jamais passer de `DateTime` à `setCreatedAtValue()`** — utiliser `DateTimeImmutable` ou laisser null (lifecycle callbacks).
- **Préférer un tableau formaté** à la sérialisation Doctrine quand les champs ciblés sont sur des entités relationnelles non couvertes par le groupe de sérialisation.
