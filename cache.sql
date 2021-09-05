BEGIN TRANSACTION;
DROP TABLE IF EXISTS "twedu";
CREATE TABLE IF NOT EXISTS "twedu" (
	"educode"	TEXT,
	"glyph"	TEXT,
	"related"	TEXT,
	PRIMARY KEY("educode")
);
COMMIT;
