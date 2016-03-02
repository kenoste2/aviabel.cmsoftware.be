CREATE TABLE SUBDEBTORS (
  SUPER_DEBTOR_ID int REFERENCES FILES$DEBTORS(DEBTOR_ID),
  SUB_DEBTOR_ID int REFERENCES FILES$DEBTORS(DEBTOR_ID)
);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('subdebtors_c','all','Subklanten','Sous-clients','Subclients',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('super_debtor_c','all','Hoofdklant','Client principal','Parent client',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('subdebtor_invalid_c','all','Een klant kan niet zichzelf of een van z''n subklanten als hoofdklant hebben.','Un client ne peut pas avoir lui-même ou un des ses sous-client comme client principal.','A client can not have themselves or one of their subclients as a parent client.',0);
