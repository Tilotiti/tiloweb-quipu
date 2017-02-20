# Usage

This Bundle integrate the Quipu/Zyfro API v1 as a Symfony Service.

# Requirments

* cURL
* Symfony >= 3.1

# Intallation

## Step 1: Download the Bundle


Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require tiloweb/quipu-bundle "dev-master"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.


## Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Tiloweb\QuipuBundle\QuipuBundle(),
        );

        // ...
    }

    // ...
}
```

# Step 3: Configure Symfony


```yml
# app/config/config.yml

quipu:
    app: "yourAppID"
    secret: "yourAppSecretToken"
```

# Step 4: Enjoy !

The API is now reachable from the `quipu` service in your Controller or anywhere in Symfony.

# Documentation

## List all the contacts

```php
<?php
// src/AppBundle/Controller/DefaultController.php

public function quipuAction() {
    $quipu = $this->get("quipu");
    
    dump($quipu->listContacts());
}
```

## Create a contact


```php
<?php
// src/AppBundle/Controller/DefaultController.php

public function quipuAction() {
    $quipu = $this->get("quipu");
    
    dump($quipu->createContact(array(
        'name' => 'New Contact', // Required
        'email' => 'fakemail@test.com',
        'contry_code' => 'fr' // Required
    )));
}
```

## Get a contact

```php
<?php
// src/AppBundle/Controller/DefaultController.php

public function quipuAction() {
    $quipu = $this->get("quipu");
    
    dump($quipu->getContact(13423);
}
```
## Update a contact

```php
<?php
// src/AppBundle/Controller/DefaultController.php

public function quipuAction() {
    $quipu = $this->get("quipu");
    
    dump($quipu->updateContact(13423, array(
        'name' => 'New name',
        'email' => 'anotheremail@test.com'
    )));
}
```

## List all the invoices

```php
<?php
// src/AppBundle/Controller/DefaultController.php

public function quipuAction() {
    $quipu = $this->get("quipu");
    
    dump($quipu->listInvoices());
}
```

## List all the invoices of a contact

```php
<?php
// src/AppBundle/Controller/DefaultController.php

public function quipuAction() {
    $quipu = $this->get("quipu");
    
    dump($quipu->listInvoices(12345));
}
```

## Create an invoice


```php
<?php
// src/AppBundle/Controller/DefaultController.php

public function quipuAction() {
    $quipu = $this->get("quipu");
    
    $contactID = 12345;
    $datetime = new \DateTime();
    
    $invoice = array(
        'kind' => 'income', // Required 
        'issue_date' => $datetime->format('Y-m-d') // Required
    );
    
    $items = array(
        array(
            'concept' => 'Product 1',
            'unitary_amount' => "10",
            'quantity' => 30,
            'vat_percent' => 20,
            'retention_percent' => 0
        ),
        array(
            'concept' => 'Product 2',
            'unitary_amount' => "5",
            'quantity' => 10,
            'vat_percent' => 20,
            'retention_percent' => 0
        )
    );
    
    dump($quipu->createInvoice($contactID, $invoice, $items));
}
```

## Get an invoice

```php
<?php
// src/AppBundle/Controller/DefaultController.php

public function quipuAction() {
    $quipu = $this->get("quipu");
    
    dump($quipu->getInvoice(13423);
}
```
## Update a contact

```php
<?php
// src/AppBundle/Controller/DefaultController.php

public function quipuAction() {
    $quipu = $this->get("quipu");
    
    $datetime = new DateTime();
    
    dump($quipu->updateInvoice(13423, array(
        'paid_at' => $datetime->format('Y-m-d')
    )));
}
```