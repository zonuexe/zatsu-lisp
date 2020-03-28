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
        $simplefied = [];
        foreach ($args as $a) {
            if (!is_array($a)) {
                $simplefied[] = $a;
                continue;
            }

            $op = array_shift($a);
            $simplefied[] = $this->dispatch($op, $a);
        }

        if ($op === '+') {
            return array_sum($simplefied);
        }

        throw new RuntimeException("'{$op} function is not implemented.");
    }
}
