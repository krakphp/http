<?php

namespace Krak\Mw;

use function iter\map,
    iter\chain;

/** compose a set of middleware into a handler */
function compose(array $mws, $last = null) {
    $last = $last ?: function() {
        throw new \RuntimeException("Last middleware was reached. No handlers were found.");
    };

    return array_reduce($mws, function($acc, $mw) {
        return function(...$params) use ($acc, $mw) {
            $params[] = $acc;
            return $mw(...$params);
        };
    }, $last);
}

/** group a set of middleware into one */
function group(array $mws) {
    return function(...$params) use ($mws) {
        list($params, $next) = _splitArgs($params);

        $handle = compose($mws, $next);
        return $handle(...$params);
    };
}

/** lazily create the middleware once it needs to be executed */
function lazy($mw_gen) {
    return function(...$params) use ($mw_gen) {
        static $mw;
        if (!$mw) {
            $mw = $mw_gen();
        }

        return $mw(...$params);
    };
}

function filter($mw, $predicate) {
    return function(...$all_params) use ($mw, $predicate) {
        list($params, $next) = _splitArgs($all_params);
        if ($predicate(...$params)) {
            return $mw(...$all_params);
        }

        return $next(...$params);
    };
}

/** higher the sort, the sooner it will execute in the stack */
function stackEntry($mw, $sort = 0, $name = null) {
    return [$mw, $sort, $name];
}

function stack(array $entries = []) {
    return MwStack::createFromEntries($entries);
}

/** merges multiple stacks together into a new stack */
function stackMerge(...$stacks) {
    /** merge stacks together */
    $entries = chain(...map(function($stack) {
        return $stack->getEntries();
    }, $stacks));

    return MwStack::createFromEntries($entries);
}

class MwStack implements \Countable
{
    private $entries;
    private $heap;
    private $name_map;

    public function __construct() {
        $this->entries = [];
        $this->heap = new \SplMinHeap();
        $this->name_map = [];
    }

    public function count() {
        return count($this->entries);
    }

    public function push(...$params) {
        return $this->insertEntry(stackEntry(...$params), 'array_push');
    }
    public function unshift(...$params) {
        return $this->insertEntry(stackEntry(...$params), 'array_unshift');
    }

    /** insert a middleware before the given middleware */
    public function before($name, $mw, $mw_name = null) {
        if (!array_key_exists($name, $this->name_map)) {
            throw new \InvalidArgumentException(sprintf('Middleware %s does not exist', $name));
        }

        $sort = $this->name_map[$name];
        return $this->unshift($mw, $sort, $mw_name);
    }
    /** insert a middleware after the given middleware  */
    public function after($name, $mw, $mw_name = null) {
        if (!array_key_exists($name, $this->name_map)) {
            throw new \InvalidArgumentException(sprintf('Middleware %s does not exist', $name));
        }

        $sort = $this->name_map[$name];
        return $this->push($mw, $sort, $mw_name);
    }

    private function insertEntry($entry, $insert) {
        list($mw, $sort, $name) = $entry;
        if ($name) {
            // if we are pushing a named middleware, remove the old one so that
            // we don't have any duplicates
            $this->remove($name);
            $this->name_map[$name] = $sort;
        }

        if (!isset($this->entries[$sort])) {
            $this->entries[$sort] = [];
            $this->heap->insert($sort);
        }

        $insert($this->entries[$sort], $entry);
        return $this;
    }

    public function shift($sort = 0) {
        return $this->removeEntry($sort, 'array_shift');
    }

    public function pop($sort = 0) {
        return $this->removeEntry($sort, 'array_pop');
    }

    public function remove($name) {
        if (!array_key_exists($name, $this->name_map)) {
            return;
        }

        $sort = $this->name_map[$name];
        $index = $this->findEntryByName($this->entries[$sort], $name);
        unset($this->name_map[$name]);
        return $this->removeEntry($sort, function(&$entries) use ($index) {
            $entry = $entries[$index];
            unset($entries[$index]);
            return $entry;
        });
    }

    private function removeEntry($sort, $remove) {
        if (!isset($this->entries[$sort])) {
            return;
        }

        $entries = $this->entries[$sort];
        $entry = $remove($entries);

        $this->updateEntries($entries, $sort);

        return $entry;
    }

    /** normalizes the stack into an array of middleware to be used
        with mw\compose. */
    public function normalize() {
        $heap = new \SplMinHeap();
        $mws = [];
        foreach ($this->heap as $sort) {
            $heap->insert($sort);
            $entries = $this->entries[$sort];
            foreach ($entries as $entry) {
                $mws[] = $entry[0];
            }
        }

        $this->heap = $heap;

        return $mws;
    }

    /** allows the stack to be used once... as a middleware */
    public function __invoke(...$params) {
        $mw = group($this->normalize());
        return $mw(...$params);
    }

    public function compose() {
        return compose($this->normalize());
    }

    public function getEntries() {
        foreach ($this->entries as $entries) {
            foreach ($entries as $entry) {
                yield $entry;
            }
        }
    }

    public static function createFromEntries($entries) {
        $stack = new self();
        foreach ($entries as $entry) {
            $stack->insertEntry($entry, 'array_push');
        }
        return $stack;
    }

    private function updateEntries($entries, $sort) {
        if (!$entries) {
            unset($this->entries[$sort]);
            $this->heap = _filterHeap($this->heap, function($val) use ($sort) {
                return $val !== $sort;
            });
        } else {
            $this->entries[$sort] = $entries;
        }
    }

    private function findEntryByName($entries, $name) {
        foreach ($entries as $key => $entry) {
            if ($entry[2] == $name) {
                return $key;
            }
        }
    }
}

function _splitArgs($params) {
    $next = end($params);
    return [array_slice($params, 0, -1), $next];
}

function _filterHeap(\SplMinHeap $heap, $predicate) {
    $new_heap = new \SplMinHeap;
    foreach ($heap as $v) {
        if ($predicate($v)) {
            $new_heap->insert($v);
        }
    }
    return $new_heap;
}
