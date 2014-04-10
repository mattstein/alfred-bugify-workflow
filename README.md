Use this Alfred workflow to quickly browse Bugify issues via API. This assumes you have a single install, and requires that you first set your Bugify URL and API key. I'm not sure if there's a neater way to do this, so for now it takes two steps:

1. `setbugifyurl http://yourbugifyurl.com/` (with trailing slash)
2. `setbugifykey YOUR_BUGIFY_API_KEY`

Once those details are stored, you can use any of the following:

- `bugify projects` to list projects, then select one to view issues for that project
- `bugify users` to list users, where selecting one will list issues for that user
- `bugify filters` to list your custom filters and then view issues within them
- `bugify search KEYWORD` or just `bugify KEYWORD` to search issues

I cobbled together the status icons and happily stole the others from [Bugify](https://bugify.com/) and [Entypo](http://www.entypo.com/).

Any thoughts, crticisms, and pull requests welcome!
