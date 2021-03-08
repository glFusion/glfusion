# Contribute to glFusion

This guide details how to contribute to glFusion.  The glFusion development team welcomes all input, be bug reporting, testing, documentation updates, helping others in the forums, or patches to resolve issues or improve functionality.  glFusion utilizes the **Fork & Pull Model** for patches and code modifications.  See the details below on how to submit code modifications to glFusion.

## Fork & Pull Model

The Fork & Pull Model lets anyone fork the glFusion repository and push changes to their personal fork without requiring access be granted to the source repository. The changes must then be pulled into the main glFusion repository by the glFusion development team. It is a good idea to subscribe to the glFusion Development Discussion Mailing List to discuss your plans before starting to work.

## Pull requests

We welcome pull requests with fixes and improvements to glFusion's code.

Pull requests can be filed at [glFusion's Github Page](https://github.com/glFusion/glfusion/pulls).

### Pull request guidelines

Please submit a pull request with the fix or improvements after you have fully tested your changes. The workflow for a pull request is as follows:

1. Fork the glFusion project on Github
1. Create a feature branch
1. Add your changes to the [ChangeLog](https://github.com/glFusion/glfusion/blob/develop/CHANGELOG.md)
1. If you have multiple commits please combine them into one commit by [squashing them](http://git-scm.com/book/en/Git-Tools-Rewriting-History#Squashing-Commits)
1. Push the commit to your fork
1. Submit a pull request (PR) to the **develop** branch
1. The PR title should describes the change you want to make
1. The PR description should give a reason for your change and the method you used to achieve it
1. If the PR changes the UI it should include before and after screenshots
1. Link relevant [issues or feature requests](https://github.com/glFusion/glfusion/issues) from the pull request description and leave a comment on them with a link back to the PR
1. Be prepared to answer questions and incorporate feedback even if requests for this arrive weeks or months after your PR submission.

Please keep the change in a single PR **as small as possible**. If you want to contribute a large feature think very hard what the minimum viable change is. Can you split functionality? The smaller a PR is the more likely it is it will be merged, after that you can send more PR's to enhance it.

**Please format your pull request description as follows:**

1. What does this PR do?
2. Are there points in the code the reviewer needs to double check?
3. Why was this PR needed?
4. What are the relevant issue numbers / Feature Requests?
5. Screenshots (If appropiate)

## Contribution acceptance criteria

1. The change is as small as possible (see the above paragraph for details)
2. Has been properly tested
3. Documentation updates are provided if necessary
4. Can merge without problems (if not please use: `git rebase develop`)
5. Does not break any existing functionality
6. Fixes one specific issue or implements one specific feature (do not combine things, send separate pull requests if needed)
7. Keeps the glFusion code base clean and well structured
8. Contains functionality we think other users will benefit from too
9. Contains a single commit (please use `git rebase -i` to squash commits)

## Resources

* [How to Fork a Repository](https://help.github.com/articles/fork-a-repo)
* [Syncing a Fork](https://help.github.com/articles/syncing-a-fork)
* [Creating a Pull Request](https://help.github.com/articles/creating-a-pull-request)
