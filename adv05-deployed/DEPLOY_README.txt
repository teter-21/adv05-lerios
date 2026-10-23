ACT7 PHP Render Deployment

1) Upload the contents of this folder as the ROOT of a GitHub repository.
2) Create an Aiven MySQL service and import the original 4alerios (3).sql dump into the Aiven database selected by DB_NAME.
3) Download Aiven CA certificate and add it to Render as a secret file, /etc/secrets/ca.pem.
4) Render -> New -> Web Service -> GitHub repository -> Language: Docker; Dockerfile path: ./Dockerfile.
5) Environment variables in Render:
   DB_HOST = Aiven MySQL host
   DB_PORT = Aiven MySQL port
   DB_USER = Aiven username
   DB_PASSWORD = Aiven password
   DB_NAME = the database into which the dump was imported (e.g., defaultdb)
   DB_SSL_CA_PATH = /etc/secrets/ca.pem
6) Deploy and open https://<your-service>.onrender.com. The index redirects to login.php.

CAUTION: Render Free instances have ephemeral disks. New user/instructor photos uploaded at runtime will disappear on restart/redeploy. For persistent uploads, use external object storage or a paid persistent disk and adapt the upload code. The bundled existing photos are retained as long as included in the Git repository.

SECURITY: Do not commit SQL dump, CA credentials or passwords into public Git. Replace default/test credentials before making public, and only deploy if you are authorized to publish this user data.
