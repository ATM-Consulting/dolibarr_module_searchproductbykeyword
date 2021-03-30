# CHANGELOG FOR XXXXXX MODULE

## Not Released

- FIX : Untimely CSRF token renewal by js script *2021-03-30* - 1.0.1
    Dolibarr v13 enforces CSRF protection. With each page load by the
    user, a token is generated and embedded in any forms on the page.
    If the token is renewed (like here by a script that includes
    main.inc.php) AFTER the initial token was embedded on the form,
    the token check will always fail. Therefore it is important to
    prevent CSRF token renewal in page dependencies (scripts, css or
    ajax backends).

## 1.0.0

- No ChangeLog existed for this release
