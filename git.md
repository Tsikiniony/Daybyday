### Initialize git
git init
git add README.md   
git commit -m "first commit"    
git branch -M main      
git remote add origin https://github.com/Tsikiniony/Daybyday.git
git add . 
git commit -m "Project commit" 
git push origin main

### Update git
git pull origin main --rebase
### Create branch
git checkout -b name

### Push branch
git add . 
git commit -m "Project commit" 
git push origin name