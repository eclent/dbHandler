
# dbHandler Class

This class manages the database connection using PDO, executes SQL queries (SELECT, INSERT, UPDATE, DELETE), handles transactions, and cleans input data to enhance security.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [getQuery](#getquery)
- [execQuery](#execquery)
- [Transactions](#transactions)
- [logQuery](#logquery)
- [References](#references)

## Installation

Simply copy all the files to a directory in your project (for example, "/include").

## Configuration

Edit the `config.php` and add your database connection parameters. You can also enable or disable the query debugging option.

```php
<?php
// config.php

return [
  'database' => [
    'servername' => 'your_db_server',
    'dbname' => 'your_db_name',
    'username' => 'your_db_username',
    'password' => 'your_db_password'
  ],
  'debugQuery' => true, // Set to true to enable query debugging
];

?>
```

## Usage

### Initialization

Include the `dbHandler` class in your project and instantiate it as needed.

```php
require 'path/to/dbHandler.php';

$db = new dbHandler();
```

### getQuery

Executes a SQL SELECT statement and returns the results.

#### Parameters
- `string $query`: The SQL query to be executed.
- `array|null $data`: The data to be bound to the query parameters (default: null).
- `bool $fetchOne`: Determines whether to fetch one row (true) or all rows (false) (default: false).

#### Returns
- `array`: The fetched results.

#### Example

```php
$query = 'SELECT * FROM users WHERE name = :name AND surname = :surname';
$data = array(
  ':name' => 'Tony',
  ':surname' => 'Stark'
);
$result = $db->getQuery($query, $data, true);

print_r($result);
```

### execQuery

Executes a SQL INSERT, UPDATE, or DELETE statement and returns the result.

#### Parameters
- `string $query`: The SQL query to be executed.
- `array|null $data`: The data to be bound to the query parameters (default: null).

#### Returns
- `number|bool`: The execution result (last inserted ID for INSERT, true for successful UPDATE/DELETE, false otherwise).

#### Example

```php
$query = 'INSERT INTO users (name, email) VALUES (:name, :email)';
$data = array(
  ':name' => 'Tony Stark',
  ':email' => 'tony.stark@example.com'
);
$insertID = $db->execQuery($query, $data);

echo $insertID;
```

### Transactions

Handle transactions composed of multiple queries.

#### Example

```php
try {

  $db->beginTransaction(); // Starts a transaction.

  $queryInsert = 'INSERT INTO payments (source, amount, user_id) VALUES (:source, :amount, :user_id)';
  $dataInsert = array(
    ':source' => 'Credit Card',
    ':amount' => 300,
    ':user_id' => 3
  );
  $insertResultID = $this->db->execQuery($queryInsert, $dataInsert);

  $queryUpdate = 'UPDATE users SET status = :status, payment_id = :payment_id WHERE user_id = :user_id';
  $dataUpdate = array(
    ':status' => 'active',
    ':payment_id' => $insertResultID,
    ':user_id' => 3
  );
  $this->db->execQuery($queryUpdate, $dataUpdate);

  $db->commit(); // Commits the transaction.

} catch (\Exception $e) {

  if ($db->inTransaction()) { //Checks if a transaction is still active.
    $db->rollBack(); // Rolls back the transaction.
  }

}
```

### logQuery

Logs a query with bound parameters for debugging purposes.

#### Parameters
- `string $query`: The SQL query to be logged.
- `array|null $data`: The data to be bound to the query parameters (default: null).

#### Returns
- `string|null`: The logged query or null if debugging is disabled.

#### Example

```php
$query = 'SELECT * FROM users WHERE name = :name AND surname = :surname';
$data = array(
  ':name' => 'Tony',
  ':surname' => 'Stark'
);
$loggedQuery = $db->logQuery($query, $data);

echo $loggedQuery;
```

## References

- [PDO Tutorial](https://phpdelusions.net/pdo)
- [PHP PDO Prepared Statements to Prevent SQL Injection](https://websitebeaver.com/php-pdo-prepared-statements-to-prevent-sql-injection)
- [Stack Overflow: Are PDO prepared statements sufficient to prevent SQL injection?](https://stackoverflow.com/questions/134099/are-pdo-prepared-statements-sufficient-to-prevent-sql-injection?rq=1)
- [PDO - PHP: The Right Way](https://guidaphp.it/base/database/pdo)
