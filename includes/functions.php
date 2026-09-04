<?php

function base_url($path = '')
{
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}