<?php

namespace Arokettu\IsResource;

/**
 * Finds whether a variable is a resource
 *
 * @param mixed|resource|object $value The variable being evaluated.
 * @return bool true if var is a resource, false otherwise.
 * @see \is_resource()
 */
function is_resource($value)
{
    // pass non objects to the vanilla function
    if (!\is_object($value)) {
        return \is_resource($value);
    }

    $resourceMap = ResourceMap::map();
    $class = \get_class($value);

    return isset($resourceMap[$class]) && \extension_loaded($resourceMap[$class][0]);
}

/**
 * Returns the resource type
 *
 * @param resource|object $resource The evaluated resource handle or opaque object that replaced it.
 * @return string|null If the given handle is a resource, this function
 * will return a string representing its type. If the type is not identified
 * by this function, the return value will be the string
 * Unknown.
 * @see \get_resource_type()
 */
function get_resource_type($resource)
{
    if (\is_object($resource)) {
        $resourceMap = ResourceMap::map();
        $class = \get_class($resource);

        if (isset($resourceMap[$class]) && \extension_loaded($resourceMap[$class][0])) {
            return $resourceMap[$class][1];
        }
    }

    // let it fail in the vanilla function for unknown classes too
    return \get_resource_type($resource);
}

/**
 * Returns the resource type
 *
 * @param resource|object|mixed $resource $resource The evaluated resource handle or opaque object that replaced it.
 * @return string|null null if $resource is not a resource or an opaque object, same as get_resource_type() otherwise.
 * @see \Arokettu\IsResource\get_resource_type()
 */
function try_get_resource_type($resource)
{
    if (\is_resource($resource)) {
        return \get_resource_type($resource);
    }

    if (\is_object($resource)) {
        $resourceMap = ResourceMap::map();
        $class = \get_class($resource);

        if (isset($resourceMap[$class]) && \extension_loaded($resourceMap[$class][0])) {
            return $resourceMap[$class][1];
        }
    }

    return null;
}
