# Solutions pour corriger l'erreur "Maximum execution time exceeded"

## 1. Configuration déjà appliquée

✅ **public/index.php** - Augmentation du temps d'exécution à 120 secondes
✅ **public/.htaccess** - Configuration Apache pour 120 secondes
✅ **Code optimisé** - Validations nullables ajoutées pour éviter les exceptions

## 2. Configuration PHP-FPM (si vous utilisez Nginx + PHP-FPM)

Éditez votre fichier `php.ini` (généralement dans `/etc/php/8.x/fpm/php.ini`):

```ini
max_execution_time = 120
max_input_time = 120
memory_limit = 256M
```

Puis redémarrez PHP-FPM:
```bash
sudo service php8.1-fpm restart
# ou
sudo systemctl restart php8.1-fpm
```

## 3. Configuration pour Windows (XAMPP/WAMP)

Éditez le fichier `php.ini` dans votre installation XAMPP/WAMP:

```ini
max_execution_time = 120
max_input_time = 120
memory_limit = 256M
post_max_size = 50M
upload_max_filesize = 50M
```

Redémarrez Apache depuis le panneau de contrôle XAMPP/WAMP.

## 4. Configuration Symfony (facultatif)

Dans le fichier `config/packages/framework.yaml`, vous pouvez aussi configurer:

```yaml
framework:
    http_client:
        default_options:
            timeout: 60
            max_duration: 120
```

## 5. Pour les appels API Momo spécifiquement

Dans `PaiementProService.php`, les méthodes `getMomoToken()` et `initiateMomoPayment()` 
utilisent `HttpClientInterface` qui peut timeout. Assurez-vous que:

- L'API Momo répond rapidement
- Votre connexion internet est stable
- Les credentials Momo sont corrects

## 6. Solutions supplémentaires si le problème persiste

### A. Augmenter le timeout pour les requêtes HTTP

Dans `config/packages/framework.yaml`:

```yaml
framework:
    http_client:
        scoped_clients:
            momo.client:
                base_uri: 'https://proxy.momoapi.mtn.com'
                timeout: 60
                max_duration: 90
```

### B. Utiliser le processing asynchrone

Pour les opérations longues, considérez l'utilisation de Symfony Messenger:

```php
// Dans votre contrôleur
$this->bus->dispatch(new InitierPaiementMessage($data));
```

### C. Vérifier les logs

Vérifiez les logs dans `var/log/dev.log` ou `var/log/prod.log` pour identifier 
quelle partie du code prend trop de temps.

## 7. Commandes utiles

```bash
# Vérifier la configuration PHP actuelle
php -i | grep max_execution_time

# Vider le cache Symfony
php bin/console cache:clear

# Redémarrer les services (Linux)
sudo systemctl restart apache2
sudo systemctl restart php8.1-fpm
```

## 8. Pour le développement local

Vous pouvez temporairement désactiver la limite dans votre code:

```php
set_time_limit(0); // Pas de limite (UNIQUEMENT pour le dev!)
```

**⚠️ Attention:** Ne jamais utiliser `set_time_limit(0)` en production!
