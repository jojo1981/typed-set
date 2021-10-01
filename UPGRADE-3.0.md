UPGRADE FROM 2.x to 3.0
=======================

typed set data structure
-----------------

- Dependency `jojo1981/php-types` upgrade from `^2.0` to `^3.0`.
- The `getType` method of the `Collection` class will still return a string value.  
  When the collection type of is of type class the full qualified class name will be returned **WITHOUT** leading namespace separator `\`.
