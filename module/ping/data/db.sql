Create Table "Ping" (
    pi_id Integer Not Null Primary Key Autoincrement,
    pi_url Text Not Null,
    pi_name Text Not Null, /* Nom pour l'affichage */
    pi_success Boolean Not Null, /* Si le ping à réussis */
    pi_date Integer Not Null, /* La date en timestamp */
    pi_status Integer, /* Le code de retour HTTP */
    pi_message Text Not Null, /* Le message lié au code de retour HTTP */
    pi_ip Text, /* L'IP du serveur pinger */
    pi_ok Boolean Not Null /* Si le site repond bien */
);