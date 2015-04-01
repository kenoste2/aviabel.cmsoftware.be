ALTER TABLE FILES$DEBTORS
ADD EXTERNAL_AUTH_CODE VARCHAR(30);

ALTER TABLE FILES$DEBTORS
ADD EXTERNAL_AUTH_EXPIRATION TIMESTAMP;

ALTER TABLE FILES$REFERENCES
ADD DEBTOR_DISPUTE_COMMENT VARCHAR(10000);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('original_amount_c','all','Oorpronkelijk factuurbedrag','Montant original de la facture','Original invoice amount',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('total_amount_c','all','Totaalbedrag met kosten en interesten','Montant total avec frais et intérêts','Total amount with costs and interests',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('open_amount_c','all','Reeds betaald bedrag','Montant déjà payé','Amount already payed',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('overview_invoices_for_c','all','Overzicht openstaande facturen voor ','Liste des factures ouvertes','Overview of open invoices',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('dispute_comment_c','all','Geef hier eventuele opmerkingen of bezwaren in ','Suggerez vos commentaires  ici ','Overview of open invoices',0);



dispute_comment_c
add_comment_c