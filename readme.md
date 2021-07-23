# futureURL plugin

FutureURL is a WordPress plugin that enables you to create tentative, not-yet-HTML hyperlinks in your WordPress files. 

If the content contains hyperlinks in a special format, each of these links will be evaluated to see 
if the current date is later than a 'golive' date embedded in the link. For each special link, if it's currently after the golive date, the link is rewritten as a standard html link. If it's currently before the golive date, the link is stripped down to just the anchor text (in other words, there's no link there, just the text). 

This enables you to place links in a post that you don't want to go live immediately. You may have a content plan that calls for multiple posts, for example, and this way you can publish posts that are early in the series and have them link to planned posts, meaning you don't have to go back and add links later. 

The format for this specialized link is:

<-thelink.com/whatever|July 1, 2022->the anchor text<->

There are no spaces in the format. The date can be given in any format that the PHP strtotime() 
function can read. 

Questions? Hit me at robert () peakzebra.com