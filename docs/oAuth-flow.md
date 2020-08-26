###oAuthflow
1 Registratie client bij authorization server  
De client moet zich registreren bij de authorization server om toegang te krijgen 
tot data van gebruikers. Data als de 'secret', 'redirect-uri' en 'scope' worden vooraf opgeslagen.   
- redirect-uri  
Deze url moet geregistreerd zijn bij de authorization-server.  
De authorization server stuurd gebruikers alleen terug naar geregistreerde redirect-uri's
- secret  
Een 'secret' waar mee de client bewijst wie hij is tijdens een request (alleen bekend bij de client en de authorization server)  
- scope  
Een string die aan geeft voor welke data de client geauthoriseerd wordt  

De **'client'** vraagt toestemming aan de user om zijn contacten in te zien op Xmail  
De **'user'** wordt redirected naar de **'autorization-server'**  
####Request om toegang tot user's data:
http://host.docker.internal:8620/request-access?response_type=CODE&client_id=CLIENT_ID&redirect_uri=REDIRECT_URI&scope=SCOPE&state=STATE  
- **CODE**:  
'Authorization code'  
oAuh kent 4 grantTypes: authorization code, password, cient credentials, implicit    
- **CLIENT_ID**:  
De user.id waarmee je bekend bent op de **'authorization-server'**  
- **REDIRECT_URI**:  
De redirect-url die is geregistreerd op de **'authorization-server'**  
- **SCOPE**:  
String die bepaald tot welke data je geauthoriseerd wordt  
- **STATE**:  
Een unieke string. Deze krijg je terug van de **'authorization-server'** en zo kun je een response koppelen aan dit request  

##De user komt op de authorization-server  
Geeft u 'spam4All' toegang tot w contacten?  
Ja / Nee

Al de gebruiker op 'Ja' klikt wordt de user terug gestuurd naar de site van de client:
####Request terug naar client:  
http://host.docker.internal:8600/process-temp-code?code=AUTH_CODE&state=STATE  
- **AUTH_CODE**:  
Dit is een temp code waarmee de client een refresh token kan ophalen  
- **STATE**:  
Dit is de state (string) die de client als referentie naar de authorization-server  
Deze wordt nu weer terug gestruurd naar de client  

###Temp-code, Refresh-token en access-token  
De client heeft nu een 'temp code'.  
Die temp-code ruilt de client in voor een refresh-token.  
Een refresh-token is long lived  
Met het refresh-token haalt de client een access-token  
Met de access-token kan de client een request doen bij de api om data op te halen    
De access-toke is maar kort gelding (een uur of zo)  
De authorization-server kan elk soort token inactive maken door hem te expireren  



