# [SUDOBO](http://sudobo.com) automatic deployment configure guide


**Configure the production server**


Before we begin setting up Envoy, let’s quickly make sure the production server is ready for deployment.

**Create a new user**

```
# Create user deployer
$ sudo adduser deployer

# Give the read-write-execute permissions to deployer user for directory /var/www
sudo setfacl -R -m u:deployer:rwx /var/www

```
If you don’t have ACL installed on your Ubuntu server, use this command to install it:

```
sudo apt install acl
```

**SSH**

On your development or production environment:

Before you generate an SSH key, you can check to see if you have any existing SSH keys.

```
$ ls -al ~/.ssh
# Lists the files in your .ssh directory, if they exist

```

Check the directory listing to see if you already have a public SSH key.

By default, the filenames of the public keys are one of the following:

> id_rsa

> id_rsa.pub

If you don't have an existing public and private key pair, or don't wish to use any that are available to connect, then generate a new SSH key.

On your terminal.

```
$ ssh-keygen -t rsa -b 4096 -C "your_email@example.com"
```

If you see an existing public and private key pair listed (for example id_rsa.pub and id_rsa) that you would like to use to connect. You can add your SSH key to the server.

```
$ ssh-copy-id -i ~/.ssh/id_rsa user@host
```

Provide your prompt password and everything will be set.


*Add SSH key*

After you have SSH key, we need to copy the private key, which will be used to connect to our server as the deployer user with SSH, to be able to automate our deployment process:


```
# As the deployer user on server
#
# Copy the content of public key to authorized_keys
$ cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
# Copy the private key text block
$ cat ~/.ssh/id_rsa
```
Now, let’s add it to your [Gitlab](https://docs.gitlab.com/ee/ci/examples/laravel_with_gitlab_and_envoy/#add-ssh-key) or [Github](https://github.com/settings/keys).

