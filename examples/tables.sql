CREATE TABLE tbl_emailtemplate
(
  id serial NOT NULL,
  subject character varying(200),
  body text NOT NULL,
  template character varying(100) NOT NULL,
  ts_created timestamp without time zone NOT NULL DEFAULT now(),
  ts_changed timestamp without time zone NOT NULL DEFAULT now(),
  type character varying(20) NOT NULL DEFAULT 'text'::character varying,
  CONSTRAINT emailtext_pkey PRIMARY KEY (id),
  CONSTRAINT emailtext_template_key UNIQUE (template)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE tbl_emailtemplate;