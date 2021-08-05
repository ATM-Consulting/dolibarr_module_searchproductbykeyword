# CHANGELOG FOR searchproductbykeyword MODULE

## Not Released

## Version 1.0
- NEW: compatibility with Dolibarr v13-v14 - *2021-08-03* - 1.0.2
- FIX: Untimely CSRF token renewal by js script
  Dolibarr v13 enforces CSRF protection. With each page load by the
  user, a token is generated and embedded in any forms on the page.
  If the token is renewed (like here by a script that includes
  main.inc.php) AFTER the initial token was embedded on the form,
  the token check will always fail. Therefore it is important to
  prevent CSRF token renewal in page dependencies (scripts, css or
  ajax backends). - *2021-03-30* - 1.0.1
- No ChangeLog existed prior to this release
