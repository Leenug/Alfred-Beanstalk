# Alfred-Beanstalk
* * *
![Preview](http://f.cl.ly/items/0m1G431n2d1y2d2h2C1P/preview.png)

A quick and dirty Alfred workflow that allows you to list and search through your  [Beanstalk](http://beanstalkapp.com/) repos.

### Setup
* * *
Couldn't be simpler, just put your **account**, **username** and **password** into the $credentials array.

![Credentials](http://f.cl.ly/items/2n163m1O0c0v3y1m3M0F/Screen%20Shot%202013-03-25%20at%2019.49.05.PNG)

### Usage
* * *
* `bs list` - lists all repos
* `bs Search` {query} - searches for repos containing {query}

The repo clone string is copied to your clipboard, looks like:

`git clone git@account.beanstalkapp.com:/repo.git -o Beanstalk`


### Credits
* * *
* [Workflows Class](https://github.com/jdfwarrior/Workflows)
* [Beanstalk API Wrapper](https://github.com/chrisbarr/Beanstalk-PHP-API)
* [Kalexiko's Beanstalk Account!](http://kalexiko.com)

### Plans
* * *
* Global latest commit
* Commit log per repo
* View + Create SSH Keys
* Deploy changes
* Re-deploy all files (maybe)
