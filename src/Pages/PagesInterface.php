<?php
namespace Publik\Pages;

interface PagesInterface
{
    public function init();

    public function stylesheet();

    public function script();

    public function html();
}