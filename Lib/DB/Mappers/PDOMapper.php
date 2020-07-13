<?php


/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class PDOMapper {
    protected $connection;
    /** @var PDOStatement $statement */
    protected $statement;

    public function getStatement()
    {
        return $this->statement;
    }

    public function __construct($database)
    {
        try {
            $this->connection = new PDO('mysql:host='.$database['host'].';dbname='.$database['dbname'], $database['username'], $database['passwd']);
            // $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        }catch (PDOException $exception){
            throw new Exception($exception->getMessage());
        }
    }

    public function __get($name)
    {
        return $this->connection->{$name};
    }

    protected function prepare($query, $types='', $parameters=[]) {
        if ($this->statement) {
            $this->statement->closeCursor();
            unset($this->statement);
        }
        $this->statement = $this->connection->prepare($query);

        return $this->statement;
    }

    public function execute($query, $types=[], $parameters=[], &$errors=null, &$errnos=false, $line=false) {
        $this->prepare($query);
        $this->statement->execute($parameters);

        $error = $this->statement->errorInfo();
        if(is_array($error)){
            $error = array_pop($error);
        }
        $weight = ltrim($error, '0');
        if ($weight) {
            $errors[] = $error.$line;
            $errnos[] = $error;
            throw $this->createException($error.$line, $query);
        }

        if (false !== strpos(strtolower($query), 'insert')){
            $id = $this->connection->lastInsertId();
            if(!empty($id)) {
                return $id;
            }

        }

        return $this->statement;
    }

    public function fetchAssoc($query, $types='', $parameters=[], $errors=false) {
        $this->prepare($query);
        $this->statement->execute($parameters);
        $exception = $this->createException($this->statement->errorCode(), $query);
        if (!empty($error)){
            throw new $exception;
        }

        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchOneAssoc($query, $types='', $parameters=[]) {
        $this->prepare($query, $types, $parameters);
        $this->statement->execute();

        $results = $this->statement->fetchAll(PDO::FETCH_ASSOC);

        return !empty($results[0]) ? $results[0] : $results;
    }

    public function affectedRows() {
        if (!$this->statement){
            return null;
        }
        $errorCode = ltrim($this->statement->errorCode(), '0');
        $weight = ltrim($errorCode, '0');
        if (!empty($weight)){
            return false;
        }

        return $this->statement->rowCount();
    }

    public function executeRequests($requests, &$logs=[], $ignoredErrors=[]) {
        $ignoredErrors[] = '42S01';
        $errors = [];
        foreach ($requests as $request) {
            try {
                $this->prepare($request);
                $this->statement->execute();

                $error = $this->statement->errorInfo();
                $error = array_pop($error);
                $weight = ltrim($error, '0');
                if (!empty($weight) && !array_key_exists($weight, $errors) && !in_array($this->statement->errorCode(), $ignoredErrors)) {
                    $errors[$weight] = $this->statement->errorCode().',  '.$error.', '.$request;
                }
            } catch (Exception $exception) {
                $weight = ltrim($error, '0');
                if (!empty($weight) && !array_key_exists($weight, $errors) && !in_array($this->statement->errorCode(), $ignoredErrors)) {
                    $errors[$weight] = $this->statement->errorCode().',  '.$error.',    '.$exception->getMessage();
                }
            }
        }
        $logs = array_merge($logs, $errors);

        return 0 === count($errors);
    }

    public function executeOneByOne($requests, &$logs=[], $ignoredErrors=[]) {
        $errors   = [];
        $warnings = [];
        foreach ($requests as $id => $query) {
            try {
                $this->prepare($query);
                $this->statement->execute();

                $error = $this->statement->errorInfo();
                $error = array_pop($error);

                $weight = ltrim($this->statement->errorCode(), '0');
                if(!empty($weight) && !array_key_exists($weight, $errors)) {
                    if(!empty($weight) && !array_key_exists($weight, $errors) && !in_array($id, $ignoredErrors)) {
                        $weight = $this->getTableName($query).': '.$weight;
                        $errors[$weight] = $error;
                    }else{
                        $warnings[$weight] = $error;
                    }
                }
            } catch (Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }
        $logs = array_merge($logs, $errors, $warnings);
        if (count($errors)) {
            return false;
        }

        return true;
    }

    public function getTableName($query) {
        $query = strtolower($query);
        $delimiter = ord('#');
        $matches   = [];
        while (false !== strpos($query, chr($delimiter))){
            $delimiter++;
        }
        $delimiter = chr($delimiter);
        if (false !== strpos($query, 'table')){
            $pattern = $delimiter.'table(?: ).(?:`)?([A-Za-z_-]+)(?:`)?'.$delimiter;
            preg_match($pattern, $query, $matches);
            return empty($matches[1]) ?: $matches[1];
        }
    }

    public function createException($errorCode, $query)
    {
        $errorCode = ltrim($errorCode, '0');
        if (empty($errorCode)){
            return false;
        }
        $errors = $this->statement->errorInfo();
        $errors = $errors[0].', '.$errors[1].PHP_EOL.$errors[2];

        return new PDOException($errors.PHP_EOL.'QUERY: '.PHP_EOL.$query, (int) $this->statement->errorCode());
    }

}