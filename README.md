# Invigilator 

Invigilator is a quizaccess plugin to capture the user's screenshot(Entire display surface) to detect if the user is using unfair means during the Quiz. It will capture the screenshot(Entire display surface) automatically every 30 seconds and store it as a PNG image. 


This plugin will help you to capture random screenshot when the student/user is attempting the Quiz. 
Before starting the quiz, it will ask for screenshare permission. By accepting the permission you will be able to see your screenshots and you can continue to answer the questions. It will act as a video recording service like everything is capturing so the user will don't try to do anything suspicious during the exam.


## Features
- Capture screenshot of entire screen.
- Can't access quiz if the user does not allow the screenshare
- Admin report and check any suspicious activity
- Will work with existing Question Bank and Quiz
- Webservice API for external call
- Images are stored in Moodledata as a small png image


## Configuration

You can install this plugin from [Moodle plugins directory](https://moodle.org/plugins) or can download from [Github](https://github.com/eLearning-BS23/quizaccess_invigilator).

> After installing the plugin, you can use the plugin by following:


- Go to you quiz setting (Edit Quiz): 
- Change the *Extra restrictions on attempts* -> *Screenshot capture validation*  to **must be acknowledged before starting an attempt**
- Done!
```
  Dashboard->My courses->Your Course Name->Lesson->Quiz Name->Edit settings
```
<p>
<img width="800" alt="Settings" src="https://user-images.githubusercontent.com/19352999/126743993-26b5312d-7173-4f79-9d2f-fe2c212fc650.PNG">
</P>

> Now you can attempt your quiz like this:

<p>
<img width="800" alt="StartAttempt" src="https://user-images.githubusercontent.com/19352999/126743491-4edd9f35-257a-42d3-bc73-469ca05a1a63.PNG">
</p>

> You can check the report from Admin Site:

<p>
  <img width="800" alt="Report1" src="https://user-images.githubusercontent.com/19352999/126743566-c397792a-e303-4275-b155-2bbd657dffc8.PNG">
</p>

<p>
  <img width="800" alt="Report2" src="https://user-images.githubusercontent.com/19352999/126743603-a6be4c08-9229-4d4a-b8e5-ce7f41336bb1.PNG">
</p>

## License

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
