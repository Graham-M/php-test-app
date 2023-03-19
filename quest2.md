## Quest 2 - Refactor the app into IaC.


1. Build an AMI with the suggested base image.
2. Add the required packages and application to the AMI via the Ansible proivisioner.
3. Use AWS managed services, stood up by Terraform, to provide the data stores for the service.
4. Security groups should be provisioned on the basis of 'least privilege' between services.
5. Upon hitting the IP address return by your Terraform output, you should be able to get the required website (you might need to populate the database, so the app won't work on a single Terraform run! :) )
