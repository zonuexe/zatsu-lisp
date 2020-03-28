<?php

declare(strict_types=1);

namespace zonuexe\ZatsuLisp;

use RuntimeException;

final class Lisp
{
    /**
     * @param array<mixed>
     * @return mixed
     */
    public function eval(array $sexp)
    {
        $op = array_shift($sexp);
        $args = $sexp;

        return $this->dispatch($op, $args);
    }

    /**
     * @return mixed
     */
    public function dispatch(string $op, array $args)
    {
        if ($op === '+') {
            return array_sum($args);
        }

        throw new RuntimeException("'{$op} function is not implemented.");
    }
}
