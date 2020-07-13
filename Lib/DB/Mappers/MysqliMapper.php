<?php


/**
 *
 * @author Hassane SIDI AMMI <h.sidiammi@gmail.com>
 */

class MysqliMapper {
    protected $connection;
    /** @var mysqli_stmt $statement */
    protected $statement;

    public function __construct($database)
    {
        $this->connection = new mysqli($database['host'], $database['username'], $database['passwd'], $database['dbname']);

        if ($this->connection->connect_error) {
            throw new Exception($this->connection->connect_error);
        }
    }

    public function __get($name)
    {
        return $this->connection->{$name};
    }

    protected function prepare($query, $types='', $parameters=[]) {
        if ($this->statement) {
            $this->statement->close();
            unset($this->statement);
        }
        $this->statement = $this->connection->prepare($query);
        $count = 0;
        if (!$this->statement) {
            if(1146 == $this->connection->errno){
                throw new TableNotFoundException(
                    str_replace(['; ', 'near '], [';<br>', 'near:<br>'], $this->connection->error, $count).
                    ($count ? '' : '<br>'),
                    $this->connection->errno
                );
            }
            throw new RuntimeException(
                str_replace(['; ', 'near '], [';<br>', 'near:<br>'], $this->connection->error, $count).
                ($count ? '' : '<br>'),
                $this->connection->errno
            );
        }

        if (count($parameters)){
            array_unshift($parameters, $types);
            @call_user_func_array(array($this->statement, 'bind_param'), $parameters);
        }

        return $this->statement;
    }

    public function execute($query, $types=[], $parameters=[], &$errors=null, &$errnos=null, $line=false) {
        $this->prepare($query, $types, $parameters);
        $this->statement->execute();

        if ($this->statement->error) {
            if (null === $errors) {
                if (1062 === $this->errno) {
                    $error = $this->statement->error;
                    $this->statement->close();
                    unset($this->statement);
                    throw new DuplicateEntryException($error.$line);
                }

                throw new Exception($this->statement->error.$line, $this->statement->errno);
            }

            $errors[] = $this->statement->error.$line;
            if(null === $errnos){
                $errnos[] = $this->statement->errno;
            }
        }

        return $this->statement;
    }

    public function fetchAssoc($query, $types='', $parameters=[]) {
        $this->prepare($query, $types, $parameters);
        $this->statement->execute();

        return $this->statement->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchOneAssoc($query, $types='', $parameters=[]) {
        $this->prepare($query, $types, $parameters);
        $this->statement->execute();

        return $this->statement->get_result()->fetch_assoc();
    }

    public function affectedRows() {
        if (!$this->statement){
            return null;
        }
        return $this->statement->affected_rows;
    }

    public function executeRequests($requests, &$logs=[], $ignoredErrors=[]) {
        $this->connection->autocommit(false);
        $result = true;
        try {
            foreach ($requests as $request) {
                $this->connection->query($request);

                if($this->connection->errno && !in_array($this->connection->errno, $ignoredErrors)) {
                    $logs[] = $this->connection->error;
                    $result = false;
                    break;
                }
            }

            if ($result) {
                $result = $this->connection->commit();
            }

            if (!$result) {
                $this->connection->rollback();
            }
        } catch (Exception $exception) {
            $logs = $exception->getMessage();
        }
        $this->connection->autocommit(true);

        return (bool) $result;
    }

    public function executeOneByOne($requests, &$logs=[], $ignoredErrors=[]) {
    $this->connection->autocommit(false);
    $result = true;
    foreach ($requests as $request) {
        try {
            $this->connection->query($request);

            if($this->connection->errno && !in_array($this->connection->errno, $ignoredErrors)) {
                $logs[] = $this->connection->error;
            }

            if ($result) {
                $result = $this->connection->commit();
            }

            if (!$result) {
                $this->connection->rollback();
            }
        } catch (Exception $exception) {
            $logs[] = $exception->getMessage();
        }
    }

    return true;
}

}