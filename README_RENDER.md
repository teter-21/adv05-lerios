# ADV05 - Render GitHub upload

Upload the **contents** of this directory to the **root** of your GitHub repository (not the enclosing folder). In Render select Docker, Root Directory blank, Dockerfile Path `./Dockerfile`.

Set the Environment variables `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME` (`defaultdb` if imported there), and `DB_SSL_CA_PATH=/etc/secrets/ca.pem`. Add Render **Secret File** named `ca.pem` containing the Aiven CA certificate, then Save, deploy.

All PHP files are saved as UTF-8 without BOM to avoid headers-already-sent warnings. Database credentials and CA certificates must not be committed to GitHub. Uploaded images stored locally on Render Free are not persistent across redeploys/restarts.

Do not re-import SQL into an existing database to fix PHP header warnings. The shared testing password works only if you applied the password-reset SQL to the connected database. Set unique passwords before production.
