Create Table "Watcher" (
    id Integer Not Null Primary Key Autoincrement,
    module Text Not Null, /* Nom du module */
    loaded Boolean Not Null /* Si le module a bien été chargé */
);
