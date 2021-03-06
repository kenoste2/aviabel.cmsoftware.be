ALTER TABLE IMPORT$INVOICES ADD VALUTA VARCHAR (10);
ALTER TABLE IMPORT$INVOICES ADD INVOICE_DOCCODE VARCHAR (100);
ALTER TABLE IMPORT$INVOICES ADD INVOICE_DOCLINENUM VARCHAR (100);
ALTER TABLE IMPORT$INVOICES ADD INVOICE_FROMDATE DATE;
ALTER TABLE IMPORT$INVOICES ADD INVOICE_TODATE DATE;
ALTER TABLE IMPORT$INVOICES ADD INVOICE_ACNUM VARCHAR (255);
ALTER TABLE IMPORT$INVOICES ADD COLLECTOR_CODE VARCHAR (25);
ALTER TABLE IMPORT$INVOICES ADD CONTRACT_INCEPTIONDATE DATE;
ALTER TABLE IMPORT$INVOICES ADD CONTRACT_LINEOFBUSINESS VARCHAR(25);
ALTER TABLE IMPORT$INVOICES ADD CONTRACT_LEAD VARCHAR(25);
ALTER TABLE IMPORT$INVOICES ADD LEDGER_ACCOUNT VARCHAR(25);
ALTER TABLE IMPORT$INVOICES ADD CONTRACT_DESCRIPTION VARCHAR(255);
ALTER TABLE IMPORT$INVOICES ADD CONTRACT_REFERENCE VARCHAR(255);
ALTER TABLE FILES$FILES ADD CONTRACT_DESCRIPTION VARCHAR(255);
ALTER TABLE FILES$FILES ADD CONTRACT_REFERENCE VARCHAR(255);
create index IDX_CONTRACT_DESCRIPTION  on FILES$FILES computed by (CONTRACT_DESCRIPTION);
create index IDX_CONTRACT_REFERENCE  on FILES$FILES computed by (CONTRACT_REFERENCE);


ALTER TABLE FILES$REFERENCES ADD VALUTA VARCHAR (10);
ALTER TABLE FILES$REFERENCES ADD INVOICE_DOCCODE VARCHAR (100);
ALTER TABLE FILES$REFERENCES ADD INVOICE_DOCLINENUM VARCHAR (100);
ALTER TABLE FILES$REFERENCES ADD INVOICE_FROMDATE DATE;
ALTER TABLE FILES$REFERENCES ADD INVOICE_TODATE DATE;
ALTER TABLE FILES$REFERENCES ADD INVOICE_ACNUM VARCHAR (255);
ALTER TABLE FILES$REFERENCES ADD COLLECTOR_CODE VARCHAR (25);
ALTER TABLE FILES$REFERENCES ADD CONTRACT_INCEPTIONDATE DATE;
ALTER TABLE FILES$REFERENCES ADD CONTRACT_LINEOFBUSINESS VARCHAR(25);
ALTER TABLE FILES$REFERENCES ADD CONTRACT_LEAD VARCHAR(25);
ALTER TABLE FILES$REFERENCES ADD LEDGER_ACCOUNT VARCHAR(25);

ALTER TABLE FILES$REFERENCES ADD LAST_IMPORT CHAR(1);




create index IDX_VALUTA  on FILES$REFERENCES computed by (VALUTA);
create index IDX_INVOICE_DOCCODE  on FILES$REFERENCES computed by (INVOICE_DOCCODE);
create index IDX_INVOICE_DOCLINENUM  on FILES$REFERENCES computed by (INVOICE_DOCLINENUM);
create index IDX_INVOICE_FROMDATE  on FILES$REFERENCES computed by (INVOICE_FROMDATE);
create index IDX_INVOICE_TODATE  on FILES$REFERENCES computed by (INVOICE_TODATE);
create index IDX_VALUTA_INVOICE_ACNUM  on FILES$REFERENCES computed by (INVOICE_ACNUM);
create index IDX_VALUTA_COLLECTOR_CODE  on FILES$REFERENCES computed by (COLLECTOR_CODE);
create index IDX_VALUTA_CONTRACT_INCEPTIONDATE  on FILES$REFERENCES computed by (CONTRACT_INCEPTIONDATE);
create index IDX_VALUTA_CONTRACT_LINEOFBUSINESS  on FILES$REFERENCES computed by (CONTRACT_LINEOFBUSINESS);
create index IDX_VALUTA_CONTRACT_LEAD  on FILES$REFERENCES computed by (CONTRACT_LEAD);
create index IDX_LEDGER  on FILES$REFERENCES computed by (LEDGER_ACCOUNT);


create index IDX_IMINVOICE_NUMBER on IMPORT$INVOICES computed by (INVOICE_NUMBER);
create index IDX_IMREFERENCE on IMPORT$INVOICES computed by (CLIENT_NUMBER);
create index IDX_IMINVOICE_AMOUNT on IMPORT$INVOICES computed by (INVOICE_AMOUNT);
create index IDX_IMCONTRACT_LINEOFBUSINESS on IMPORT$INVOICES computed by (CONTRACT_LINEOFBUSINESS);
create index IDX_IMDEVISION_CODE on IMPORT$INVOICES computed by (DEVISION_CODE);

create index IDX_LAST_IMPORT  on FILES$REFERENCES computed by (LAST_IMPORT);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('contractDetails_c','all','contract details','Contract details','Contract details',0)#





