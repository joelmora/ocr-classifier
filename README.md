## What the project do? ##

The class is used to **classify** letters according to its type. Every letter is stored in a pdf file and has its code _(TID)_ in the header of the file:

![Letter header](https://s3-us-west-2.amazonaws.com/joelmora.s3/letter-header.png)

In order to classify properly the class uses 3rd party libraries to read that code and move that **pdf** file into a separate folder according to its type.

The logic behind the code is:

1. Uses **ghostscript** library to transform the **pdf** into a **jpg** file.

2. Uses **imagemagick** library to crop that **jpg** file into a smaller file containing only the TID code

3. Uses **tessaract-ocr** library to read that line and extract a character which represent the type.

    ![Cropped](https://s3-us-west-2.amazonaws.com/joelmora.s3/cropped.jpg)

    _(There are 4 known types of letters: **B, E, D, F**)_

4. Finally, you will have a folder for each type of letter, and inside this folder you will have the **pdf** files.


## Installation instructions [Linux]: ##

Class was made in plain `PHP` but some external libraries are **required** to work properly.

### 1. Install External Libraries: ###

Open your terminal and use `apt-get` to install the following packages:


**1.1.** `# apt-get install tesseract-ocr`

**1.2.** `# apt-get install ghostscript`

**1.3.** `# apt-get install imagemagick`



### 2.  Configure your project ###

You **MUST** set the `pathToFiles` parameter stored in you `config.json` file.

```
"pathToFiles": "/home/[PROYECT_FOLDER]/pdfs"
```

### 3.  Place the pdf files: ###

Place the pdfs that you want to classify inside the folder configured in the previous step

_(A couple of pdfs were provided for testing purpose)_

### 4.  Run the project: ###

Inside the project folder type the following command to run the class.

`# php classifier-cli.php`

If everything goes well you should have the pdf files inside a folder for each type of letters.

![Cropped](https://s3-us-west-2.amazonaws.com/joelmora.s3/final-folders.png)

