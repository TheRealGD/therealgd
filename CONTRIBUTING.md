
### Tech/Org
- Make sure to have your name setup correctly - `git config --global --edit`.
- Please use **Pull Requests** from your own fork on github(or branches in this repo if you're a new core contributor).
- **Request review** from [@psineur](https://github.com/psineur) or any recommended reviewers.
- Every PR should have issue in both branch name & PR description
- If you found a new issue that you want to work on - create an issue first.

### Migrations & DB Schema
- Any DB work & changes must first be done & thoroughly tested on local (./dev-local.sh) db
- Use `docker exec -ti DB_CONTAINER_NAME bash` and `cd ..; bin/console doctrine:migrations:diff|migrate` to work with local db
- Manually edit resulting migrations files to remove any unneccessary statements.
- Include any DB changes in PR description
- If you're changing existing entities - make sure your Test Plan includes regression testing for all pages these Entities are used in

### Where to help
- Look up issue for current upcoming milestone with 'upforgrabs' or 'helpneeded' tags

### Code of Conduct
See [CODE_OF_CONDUCT.md](/CODE_OF_CONDUCT.md) file for details.

