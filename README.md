README
=========================
[![Build Status](https://secure.travis-ci.org/eriknyk/Annotations.png?branch=master)](http://travis-ci.org/eriknyk/Annotations)

Simple and Lightweight PHP Class & Methods Annotations Reader
===

Sample class User.php

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
        function load(){
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


Sample use.

---
    include 'User.php';
    $annotations = new Annotations();
    $result = $annotations->getClassAnnotations('User');
    print_r($result);

Result:

    Array
    (
        [Defaults] => Array
            (
                [0] => Array
                    (
                        [name] => user1
                        [lastname] => sample
                        [age] => 0
                        [address] => Array
                            (
                                [country] => USA
                                [state] => NY
                            )

                        [phone] => 000-00000000
                    )

            )

        [assertResult] => Array
            (
                [0] => false
            )

        [cache] => Array
            (
                [0] => Array
                    (
                        [collation] => UTF-8
                    )

            )

    )
---

    $result = $annotations->getMethodAnnotations('User', 'create');
    print_r($result);

Result:

    Array
    (
        [Permission] => Array
            (
                [0] => view
                [1] => edit
            )

        [Role] => Array
            (
                [0] => administrator
            )

    )

---

Creating Annotated objects.
---
You can crate fast annotated objects.

Sample Annotated Classes.

---
    <?php
    // Annotation.php

    abstract class Annotation
    {
        protected $data = array();

        public function __construct($args = array())
        {
            $this->data = $args;
        }

        public function set($key, $value)
        {
            $this->data[$key] = $value;
        }

        public function get($key, $default = null)
        {
            if (empty($this->data[$key])) {
                return $default;
            }

            return $this->data[$key];
        }

        public function exists($key)
        {
            return isset($this->data[$key]);
        }
    }

---

    <?php
    // PermissionAnnotation.php
    namespace Annotation;

    class PermissionAnnotation extends Annotation
    {
    }

---

    <?php
    namespace Base\Annotation;
    // RoleAnnotation.php

    class RoleAnnotation extends Annotation
    {
    }

---

    require_once 'Annotation/Annotation.php';
    require_once 'Annotation/PermissionAnnotation.php';
    require_once 'Annotation/RoleAnnotation.php';

    $annotations->setDefaultAnnotationNamespace('\Annotation\\');
    $result = $annotations->getMethodAnnotationsObjects('User', 'create');
    print_r($result);

Result:

    Array
    (
        [Permission] => Base\Annotation\PermissionAnnotation Object
            (
                [data:protected] => Array
                    (
                        [0] => view
                        [1] => edit
                    )

            )

        [Role] => Base\Annotation\RoleAnnotation Object
            (
                [data:protected] => Array
                    (
                        [2] => administrator
                    )

            )

    )
---

