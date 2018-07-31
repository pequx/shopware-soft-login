# PHAG Soft Login `1.3.0`

## Synopsis 
The plugin provides possibility to log in customer using securely generated hash provided via http get request parameter.
Also it adds a persistent session login feature to the current account controller. It's possible to redirect user
after login to a given page/category/resource.

## User Tests 

`IMPORTANT` Before going to production set the plugin configuration to the production settings. 
Especially value of the session timeout, default is 120 seconds (due testing).

### Case 1 – Login hash generation for a new customer `FRONT-END` `ACCOUNT`

Enter following data of a new customer:
```
Privatkunde Herr Adam Mustermann adam.mustermann86@gmail.com abc1234%
Banhofpl. 2 8910 Affoltern am Alois Schweiz [-]
```
You should see a standard user page for a new user. 
Afterwards log off.

In the backend you should see a newly created user with a login hash in the `BACKEND`>`KUNDEN`>`SOFT LOGIN`

### Case 2 – Soft Login `FRONT-END`

In the `BACKEND`>`KUNDEN`>`SOFT LOGIN` click one of the users. Be sure that user "Is Active" is selected (checked). 
Copy soft login hash. Close the window. 
Open a new tab in the browser. And type `ADDRESS`/?`HASH` and press `ENTER`.
You should see login page.
Afterwards log off.

### Case 3 – Login hash regeneration for a selected user `BACK-END` `SOFT LOGIN`

In the `BACKEND`>`KUNDEN`>`SOFT LOGIN` click edit icon on the right side of a random user. 
Enter the edit mode. Click `Regenerate Hash` and move the detail window to the right (on backend "desktop" 
space inside the browser), so that you could see both: the list and the detail windows. 
You should see updated data on the list and the detail windows (in this case soft login hash values).
You should click the `Regenerate Hash` button one more time.
The hash should be updated.
You should close the window/or click any of the buttons. 

### Case 4 – Persistent login `FRONT-END` `ACCOUNT`

Select a previously created user from the backend `BACKEND`>`KUNDEN`>`KUNDEN` and enter the edit mode. 
Click the key icon next to `PASSWORT` and generate a new password. 
You should see `Folgendes Passwort wurde generiert: [PASSWORT]`.
Copy the line and write down password in a text edit/notepad. Copy the e-mail. 
Click `SPIRCHERN`. You should see a confirmation message in the right. Close the module.
In the `FRONTEND`>`ACCOUNT` page enter the previously copied data, so the user e-mail and password.
You should see `Bleiben Sie eingeloggt` box checked.
You should see a default page for a logged in user.
After `130 seconds` reload the page.
Yo u should be logged out and see a default home page.

### Case 5 – Login hash regeneration after password recovery `FRONT-END` `ACCOUNT`
-
In the [ BACKEND ]>[ KUNDEN ]>[ SOFT LOGIN ] select previously created user. Copy the email and login hash.
Close the window. 
In the [ FRONT-END ], at the [ ACCOUNT ] page click [ Passwort vergessen? ] and enter the email. Click [ E-MAIL SENDEN ].
You should see a default response page. 
In the [ BACKEND ] click on the refresh icon the the bottom. Previously selected user should have a new login hash. Enter the edit mode. The hash should be the same as on the list.

### Case 6 – Login hash regeneration after password reset `BACKEND` `KUNDEN`

In the [ BACKEND ]>[ KUNDEN ]>[ SOFT LOGIN ] select previously created user. Copy the email and login hash.
In the In the [ BACKEND ]>[ KUNDEN ]>[ KUNDEN ] select in the edit mode a previously created user. Click on the yellow key icon next to a [ PASSWORT ].
Click [ SPEICHERN ]. Close the module.
In the [ BACKEND ]>[ KUNDEN ]>[ SOFT LOGIN ] click the refresh icon on the bottom. You should see the previously created user has a new login hash.
Close the window.	

### Case 7 – Login hash regeneration after adding a new customer `BACKEND` `KUNDEN`

In the In the [ BACKEND ]>[ KUNDEN ]>[ ANLEGEN ] add a new customer with the following data:
customer123@muster.com 
Perfect Hair Kunden
[...]

