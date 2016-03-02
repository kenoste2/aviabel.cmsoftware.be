ALTER TABLE FILES$DEBTORS
ADD EXTERNAL_AUTH_CODE VARCHAR(30);

ALTER TABLE FILES$DEBTORS
ADD EXTERNAL_AUTH_EXPIRATION TIMESTAMP;

ALTER TABLE FILES$REFERENCES
ADD DEBTOR_DISPUTE_COMMENT VARCHAR(10000);

ALTER TABLE FILES$REFERENCES
ADD DISPUTE_COMMENT VARCHAR(10000);

ALTER TABLE FILES$REFERENCES
ADD DISPUTE_STATUS VARCHAR(30);

ALTER TABLE FILES$REFERENCES
ADD DISPUTE_ASSIGNEE VARCHAR(300);

ALTER TABLE FILES$REFERENCES
ADD DEBTOR_DISPUTE_EMAIL VARCHAR(300);

ALTER TABLE FILES$REFERENCES
ADD DEBTOR_DISPUTE_PHONE VARCHAR(300);

ALTER TABLE FILES$REFERENCES
ADD DISPUTE_AMOUNT DECIMAL(10);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('original_amount_c','all','Oorpronkelijk factuurbedrag','Montant original de la facture','Original invoice amount',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('total_amount_c','all','Totaalbedrag met kosten en interesten','Montant total avec frais et intérêts','Total amount with costs and interests',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('open_amount_c','all','Reeds betaald bedrag','Montant déjà payé','Amount already payed',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('overview_invoices_for_c','all','Overzicht openstaande facturen voor ','Liste des factures ouvertes','Overview of open invoices',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('external_dispute_comment_c','all','Geef hier eventuele opmerkingen of bezwaren in ','Suggerez vos commentaires  ici ','Overview of open invoices',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('add_comment_c','all','Commentaar toevoegen','Ajouter votre commentaire','Add your comment',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('invoice_in_process_c','all','Factuurbetwisting in behandeling','Dispute de la facture est en train d''être procédé','Invoice dispute is being processed',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('dispute_assignee_c','all','Verantwoordelijke','Responsable','Assignee',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('dispute_status_c','all','Betwistingsstatus','Statut de la dispute','Dispute status',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('debtor_dispute_comment_c','all','Opmerking van debiteur','Commentaire du débiteur','Debtor remark',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('no_debtor_dispute_comment_found_c','all','Er werd geen opmerking door de debiteur ingegeven','Le débiteur n''a pas ajouté un commentaire','The debtor has not provided any remarks',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('dispute_comment_c','all','Opmerkingen bij betwisting','Commentaire sur la dispute','Dispute comments',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('invite_for_external_access_c', 'all', 'Uitnodigen voor externe log-in', 'Inviter pour acces externe', 'Invite for external log-in',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('debtor_invited_c', 'all', 'De klant werd uitgenodigd per e-mail.', 'Le client à été invité par e-mail.', 'The client was invited by email.',0);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('external_dispute_email_c', 'all', 'Contact-e-mail', 'E-mail', 'Contact email.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('external_dispute_phone_c', 'all', 'Contacttelefoonnr.', 'Numéro de téléphone', 'Contact phone.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('debtor_dispute_email_c', 'all', 'Contact-e-mail', 'E-mail', 'Contact email.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('debtor_dispute_phone_c', 'all', 'Contacttelefoonnr.', 'Numéro de téléphone', 'Contact phone.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('no_debtor_dispute_phone_found_c','all','Er werd geen telefoonnr. door de debiteur ingegeven','Le débiteur n''a pas ajouté un numéro de téléphone','The debtor has not provided a phone number',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('no_debtor_dispute_email_found_c','all','Er werd geen e-mail door de debiteur ingegeven','Le débiteur n''a pas ajouté un e-mail','The debtor has not provided an email',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('dispute_amount_c','all','Betwist bedrag','Montant disputé','Disputed amount',0);


UPDATE TEKSTEN
SET NL = 'datum ingave', FR = 'date d''entrée des données', EN = 'input date'
WHERE CODE = 'dispute_date_c';

UPDATE TEKSTEN
SET NL = 'oplossen vóór', FR = 'résoudre avant le', EN = 'solve before'
WHERE CODE = 'dispute_duedate_c';

UPDATE TEKSTEN
SET NL = 'feitelijke oplosdatum', FR = 'date effective de la résolution', EN = 'effective solution date'
WHERE CODE = 'dispute_ended_c';
