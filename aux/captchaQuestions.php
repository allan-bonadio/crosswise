<?
function QQ($q, $a) {
	return array('question' => $q, 'answer' => $a);
}

array(
QQ("To check to see if two things are the same, you don't use just a =, instead you use what?", '=='),
QQ("They're not the same!  There's no not-equal char, instead you use what two chars?", '!='),
QQ("This adds, and it's just a single punctuation mark.", '+'),
QQ("This subtracts, and it's just a single punctuation mark.", '-'),
QQ("Maybe you want to do it, and maybe not.  But this statement does it, just two chars.", 'if'),
QQ("You do something or otherwise you do another thing.  What's a four letter word for otherwise?", 'else'),
QQ("Is it smaller?  Or the same?  what two symbol chars say this?", '<='),
QQ("Is it larger?  Or the same?  what two symbol chars say this?", '>='),
QQ("At the end of a function, it just ends.  But if you want to end in the middle, what word do you use?", 'return'),
QQ("At the end of a loop, it just ends.  But if you want to end in the middle, what word do you use?", 'break'),
QQ("You want to make sure both things are true.  What two chars do you put between?", '&&'),
QQ("You want to see if either of two things are true.  What two chars do you put between?", '||'),
QQ("You have several things, numbered 0, 1, 2, ....  What data structure do you keep them in?", 'array'),
QQ("This operator adds, but it only adds one.  What two chars?", '++'),
QQ("This operator subtracts, but it only subtracts one.  What two chars?", '--'),
QQ("you have to repeat, as long as something is true.  How do you say 'as long as' in one word?", 'while'),
QQ("The single char you use to multiply with?", '*'),
QQ("The punctuation mark you use to divide with?", '/'),
QQ("If you say 5 - 6 * 7, which is done first, the - or the * ?", '*'),
QQ("If you say 5 + 6 * 7, which is done first, the + or the * ?", '*'),
QQ("If you say 5 - 6 / 7, which is done first, the - or the / ?", '/'),
QQ("If you say 5 + 6 / 7, which is done first, the + or the / ?", '/'),
QQ("If you want to set x to five, which single char do you put between them?", '='),
QQ("If ar is an array, what five chars do you type to pick out element at slot 5?", 'ar[5]'),
QQ("If fun is a function, what six chars do you type to call it with an argument of 5?", 'fun(5)'),
QQ("If fun is a function, what five chars do you type to call it with no arguments?", 'fun()'),
QQ("Every object has a category.  But they don't say category, what's the word they use?", 'class'),
QQ("Classes have instances.  Those instances, what's a word that starts with o?", 'object'),
//QQ("?", ''),
)
