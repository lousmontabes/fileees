# fileees 
A simple & private file sharing app.

## How does it work?
You pop filee.es into your browser, you click on the big button in the middle and you drag and drop some files in there.

### Encryption
Anything you upload gets encrypted client-side (so *before* anything gets sent over the internet), hashed for verification, processed by the server and safely stored on an Amazon S3 instance.

Since the encryption key is stored with the folder's information, you'll only need to know the folder's token (a combination between a random adjective + noun) in order to upload files:

`filee.es/&UnmownPatio`

### Decryption
Anyone who knows your folder's token can see its contents, but in order to read any files you'll need to access from a special URL including a unique decryption key. That URL can look like the following:

`filee.es/&UnmownPatio#19258c9b0c93cba8197b121eeccb323342afabfc0765179dd2b`

Without the decryption key, any data on there is as good as garbage.

**That's it! Go to [filee.es](filee.es) to start sharing.**
