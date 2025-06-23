# Helpers

The Symfony Framework offers a Helper System.
[http://symfony.com/doc/current/components/console/introduction.html#console-helpers](http://symfony.com/doc/current/components/console/introduction.html#console-helpers)

n98-magerun2 comes with a list of special helpers for the command line.

## Database Helper

Run DB-Query:

```php
$db = $this->getHelper('database')->getConnection($output);
$db->query('DELETE FROM foo');
```

Get all tables:

```php
$tables = $this->getHelper('database')->getTables();
```

Check MySQL Privileges:

```php
$hasPrivileges = $this->getHelper('database')->mysqlUserHasPrivilege('select');
```

Get MySQL Connection String (for external MySQL cli client)

```php
$connectionString = $this->getHelper('database')->getMysqlClientToolConnectionString();
```

Get PDO DSN:

```php
$dsn = $this->getHelper('database')->dsn();
```

Return value of a MySQL variable:

```php
$waitTimeout = $this->getHelper('database')->getMysqlVariableValue('wait_timeout');
```

## Parameter Helper

Ask Store

Evaluates the parameter "store". As third parameter you can specify a different argument name if it's not the recommended name "store".

```php
$this->getHelper('parameter')->askStore($input, $output);
```

Ask for website

```php
$this->getHelper('parameter')->askWebsite($input, $output);
```

Ask for Email

```php
$this->getHelper('parameter')->askEmail($input, $output);
```

Ask for Password

```php
$this->getHelper('parameter')->askPassword($input, $output);
```

## Table Helper

```php
$table = array();
$table[] = ('line' => '1', 'firstname' => 'Peter');
$table[] = ('line' => '2', 'firstname' => 'Lena');
$this->getHelper('table')->write($output, $table);
```

## TwigHelper

Renders a Twig Template. See [Twig](https://github.com/netz98/n98-magerun2/wiki/Twig) for configuration options.

```php
$this->getHelper('twig')->render('template_name.twig', array('var1' => 'value1'));
```

Render Twig code directly.

```php
$this->getHelper('twig')->renderString('{{description | lower}} {{var1}}', array('var1' => 'value1'));
```
