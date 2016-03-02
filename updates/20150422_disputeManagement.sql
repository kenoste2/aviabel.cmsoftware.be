
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('no_disputed_invoices_found_c','all','Geen betwiste facturen gevonden.', 'Pas de factures disputés retrouvées.','No disputed invoices found.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('search_disputed_invoices_c','all','Zoek betwiste facturen', 'Recherchez des factures disputées','Search disputed invoices',0);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('setting_dispute_statusses','all','status1' || ASCII_CHAR(10) || 'status2' || ASCII_CHAR(10) || 'status3', 'status1' || ASCII_CHAR(10) || 'status2' || ASCII_CHAR(10) || 'status3','status1' || ASCII_CHAR(10) || 'status2' || ASCII_CHAR(10) || 'status3',1);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('setting_dispute_assignees','all','persoon1' || ASCII_CHAR(10) || 'persoon2' || ASCII_CHAR(10) || 'persoon3', 'persoon1' || ASCII_CHAR(10) || 'persoon2' || ASCII_CHAR(10) || 'persoon3','persoon1' || ASCII_CHAR(10) || 'persoon2' || ASCII_CHAR(10) || 'persoon3',1);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('menu_disputes_search', 'all','Zoek betwiste facturen', 'Recherchez des factures disputées','Search disputed invoices',0);

UPDATE TEKSTEN
SET NL = 'E-mails',
  FR = 'Emails',
  EN = 'Emails'
WHERE CODE = 'imported_mails_c';

UPDATE TEKSTEN
SET NL = 'Betwistingen',
  FR = 'Disputes',
  EN = 'Disputes'
WHERE CODE = 'menu_disputes_search';

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('disputes_today_c', 'all','Betwistingen vandaag', 'Disputes d''aujourd''hui','Today''s disputes',0);
