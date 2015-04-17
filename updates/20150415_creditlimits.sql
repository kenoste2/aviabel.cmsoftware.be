ALTER TABLE FILES$FILES ADD OWN_CREDIT_LIMIT DECIMAL(10,2);
ALTER TABLE FILES$FILES ADD PROVIDER_CREDIT_LIMIT DECIMAL(10,2);
ALTER TABLE FILES$FILES ADD INSURANCE_CREDIT_LIMIT DECIMAL(10,2);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('eigen_kredietlimiet_c','all','Eigen kredietimiet','Limite de crédit interne','Internal credit limit',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('kredietlimiet_provider_c','all','Kredietlimiet handelsinformant','Limite de crédit du fournisseur des informations commerciales','Credit limit of trade information provider',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('kredietlimiet_verzekering_c','all','Kredietlimiet opgegeven door kredietverzekeraar ','Limite de crédit par l`assureur crédit','Credit limit of credit insurer',0);

create index IDX_OWN_CREDIT_LIMIT  on FILES$FILES computed by (OWN_CREDIT_LIMIT);
create index IDX_PROVIDER_CREDIT_LIMIT  on FILES$FILES computed by (PROVIDER_CREDIT_LIMIT);
create index IDX_INSURANCE_CREDIT_LIMIT  on FILES$FILES computed by (INSURANCE_CREDIT_LIMIT);
