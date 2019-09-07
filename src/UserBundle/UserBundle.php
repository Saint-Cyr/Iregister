<?php

namespace UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class UserBundle extends Bundle
{
    public function getParent() {
        parent::getParent();
        return "FOSUserBundle";
    }
}
