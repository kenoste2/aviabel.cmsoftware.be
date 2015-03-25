create table PAYMENT_DELAY_AVERAGE (
    PAYMENT_DELAY_AVERAGE_ID DOM_RECORD_ID,
    PAYMENT_DELAY DECIMAL(10),
    NR_OF_PAYMENTS INT,
    DEBTOR_ID DOM_RECORD_ID,
    DATE_STAMP DOM_DATE
);

INSERT INTO FILES$STATES (STATE_ID, CODE, DESCRIPTION, ACTIEF, DELETEPOSSIBLE)
  VALUES((SELECT MAX(STATE_ID) + 1 FROM FILES$STATES), 'OVER_DELAY', 'Een van de facturen voor dit dossier is over de gemiddelde betaalperiode voor deze debiteur.', 1, 0);

INSERT INTO FILES$ACTIONS (ACTION_ID,CODE,DESCRIPTION,AMOUNT,INVOICEABLE,SYSTEM,FIRST_ACTION,COST_ID,VISIBLE,COLLECTOR,ACTIEF,FILE_STATE_ID,DELETEPOSSIBLE)
  VALUES((SELECT MAX(ACTION_ID) + 1 FROM FILES$ACTIONS), 'OVER_DELAY', 'Een van de facturen voor dit dossier is over de gemiddelde betaalperiode voor deze debiteur.', 0.00, 0,0,0,NULL,1,'N',1,(SELECT MAX(STATE_ID) FROM FILES$STATES),0);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('based_on_payments_c','all','gebaseerd op aantal betalingen','basé sur nombre de payements','based on number of payments',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('insufficient_data_for_delay_c','all','nog niet genoeg gegevens.','pas encore assez de données','not enough information available',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('forecastHistogram_c','all','Betalingsvoorspelling','Prédiction de payement','Payment forecast',0);


