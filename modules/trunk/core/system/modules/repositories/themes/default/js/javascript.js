function defaultValues(totalRepos)
{
    for(var i=0;i<totalRepos;i++){
        var chkbox = document.getElementById("repo-"+i);
        if(i==0 || i==1 || i==2 || i==3 || i==7 || i==8 || i==10 || i==14)
            chkbox.checked = true;
        else
            chkbox.checked = false;
    }
}