<?php

namespace FluxErp\States;

abstract class EndableState extends State
{
    public static bool $isEndState = false;
}
