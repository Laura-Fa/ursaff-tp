# Présentation du projet

Ce projet écrit en Symfony est une API permettant de gérer des entreprises. La liste des endpoints est la suivante :

`GET /api/companies` : spécifier le format attendu 'text/csv' ou 'application/json'

`GET /api/companies/{siren}`

`POST /api/companies`

Les deux routes suivantes sont protégées via une authentification basic dans le header. Pour les tests, le login est "demo" et le mot de passe "secret".

`PATCH /api/companies/{siren}`

`DELETE /api/companies/{siren}`

Le JSON attendu d'une entreprise est :

```
{
	Siren : « XXXX » //9 chiffres,
	Raison_sociale : « XX XXX XX »,//Non vide
	Adresse :{
		Num : 3,
		Voie : « XXXXX »
		Code_postale : « XXXXX » //5 chiffres,
		Ville : « XXXX »//Non vide
		GPS : {
			Latitude : « XX.XX »,
			Longitude : « XX.XX »
        }
    }
}
```

# Installation

Pour installer les dépendances, lancez la commande `composer install`.

Pour démarrer le serveur : `symfony server:start`

# CI/CD
