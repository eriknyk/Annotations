<?php
/**
 * @author Somebody
 * @version 1.0
 *
 * @ChangeTrackingPolicy ("DEFERRED_IMPLICIT")
 * @ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ChangeTrackingPolicy("NOTIFY")
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn( name="discr", type=" string " )
 * @Table(name="ecommerce_products", indexes={name="sexarch_idx", column="name"}, variant=false)
 */
class Group
{
    /**
     * @Attribute(firstname)
     * @Attribute(lastname)
     * @Cache(max_time = 50)
     * @testAll(bool_var = false, int_var = 12345, float_var = 12345.6789, str_var = 'hello', str_woq=word,str_wq='hello word')
     * @testAll(name=erik, age=27, address={city="La paz", country="Bolivia", avenue='El Prado', building='Alameda', floor=15, dep_num=7}, phone=1234567890)
     * @Description("Your system 升级难道每次都要卸载重装？")
     *
     * @param $id string user id
     * @return bool result at build user structure process
     */
    public function build($id)
    {
    }

    /**
     * retrieve all users objects
     *
     * @setLimit(50)
     * @composed("assoc")
     * @filter(status="active")
     *
     * @return array $list array containing all users objects
     */
    public function getAll()
    {
    }

    /**
     * @throwsError(err_var={one=1, two=2)
     */
    public function errFunc1()
    {
    }

    /**
     * @throwsError(err_var="some val, var1="val1")
     */
    public function errFunc2()
    {
    }

    /**
     * @sample(err_var="1 + 1 = 2, 2+2 = 4", test_var = 'log text, {0}={1} to params...', sample={a=1,b=2,c=3})
     */
    public function shouldworks()
    {
    }
}

