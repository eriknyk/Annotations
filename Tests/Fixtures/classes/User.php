<?php

/**
 * @Defaults(name="user1", lastname = "sample", age='0', address={country=USA, state=NY}, phone="000-00000000")
 * @assertResult(false)
 * @cache(collation = UTF-8)
 */
class User
{
    /**
     * @cache(true)
     * @type(json)
     * @limits(start=10, limit=50)
     */
    public function load()
    {
    }

    /**
     * create a record
     *
     * @Permission(view)
     * @Permission(edit)
     * @Role(administrator)
     */
    public function create()
    {
    }
}

