<?php

namespace App\Ldap;

namespace App\Ldap;

use LdapRecord\Models\OpenLDAP\User as LdapUser;

class User extends LdapUser
{
    public static array $objectClasses = ['top', 'person', 'organizationalPerson', 'inetOrgPerson'];

    // Tambahkan ini untuk memaksa LdapRecord mencari berdasarkan UID
    protected string $guidKey = 'uid';

    // Tambahkan baris ini juga sebagai cadangan
    public static string $attribute = 'uid';
}
