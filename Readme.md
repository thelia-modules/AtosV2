# AtosV2 Payment Module
------------------------

## English instructions

This module offers to your customers the AtosV2 payment system, which is used, for example, by BNP Paribas.

The module is based on the "Connecteur POST" (more details here : https://documentation.sips.worldline.com/fr/WLSIPS.317-UG-Sips-Paypage-POST.html )

The module supports simplified 1.0 migrated accounts. 

### Installation

#### Manually

Install the AtosV2 module using the Module page of your back office to upload the archive.

You can also extract the archive in the `<thelia root>/local/modules` directory. Be sure that the name of the module's directory is `AtosV2` (and not `AtosV2-master`, for example).

Activate the module from the Modules page of your back-office.

The module is pre-configured with test shop data (see details here : https://documentation.sips.worldline.com/fr/, and test card data here https://documentation.sips.worldline.com/fr/cartes-de-test.html). 

#### composer

```
$ composer require thelia/atosv2-module:~1.0
```

### Usage

You have to configure the AtosV2 module before starting to use it. To do so, go to the "Modules" tab of your Thelia back-office, and activate the AtosV2 module.

Then click the "Configure" button, and enter the required information. In most case, you'll receive your merchant ID by e-mail, and you'll receive instructions to download your secret key.

The module performs several checks when the configuration is saved, especially the execution permissions on the AtosV2 binaries.

During the test phase, you can define the IP addresses allowed to use the AtosV2 module on the front office, so that your customers will not be able to pay with AtosV2 during this test phase. 

A log of AtosV2 post-payment callbacks is displayed in the configuration page.

## Instructions en français

Ce module permet à vos clients de payer leurs commande par carte bancaire via la plate-forme AtosV2, utilisée par exemple, par la BNP Paribas.

Le module est basé sur le "Connecteur POST" (plus de détails technique ici: https://documentation.sips.worldline.com/fr/WLSIPS.317-UG-Sips-Paypage-POST.html)

Le module prend en charge les migrations simplifiées de comptes en version 1.0

## Installation

### Manuellement

Installez ce module directement depuis la page Modules de votre back-office, en envoyant le fichier zip du module.

Vous pouvez aussi décompresser le module, et le placer manuellement dans le dossier ```<thelia_root>/local/modules```. Assurez-vous que le nom du dossier est bien ```AtosV2```, et pas ```AtosV2-master```

Le module est préconfiguré avec les données renseignées dans la documentation de SIPS (plus de détails ici : https://documentation.sips.worldline.com/fr/, les détails sur les cartes de test sont ici : https://documentation.sips.worldline.com/fr/cartes-de-test.html)

### composer

```
$ composer require thelia/atosv2-module:~1.0
```


## Utilisation

Pour utiliser le module AtosV2, vous devez tout d'abord le configurer. Pour ce faire, rendez-vous dans votre back-office, onglet Modules, et activez le module AtosV2.

Cliquez ensuite sur "Configurer" sur la ligne du module, et renseignez les informations requises. Dans la plupart des cas, l'ID Marchand vous a été communiqué par votre banque par e-mail, et vous devez recevoir les instructions qui vous permettront de télécharger la clef secrète.

Lors de la phase de test, vous pouvez définir les adresses IP qui seront autorisées à utiliser le module en front-office, afin de ne pas laisser vos clients payer leur commandes avec AtosV2 pendant cette phase.
