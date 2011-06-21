db.addUser("theadmin", "anadminpassword");
db.auth("theadmin", "anadminpassword");

db2 = db.getSisterDB("projectx");
db2.addUser("joe", "passwordForJoe");
db2.addUser("guest", "passwordForGuest", true);
