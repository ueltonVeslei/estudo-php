function setSkyhubCustomVariables(asyncStack) {
    var skParam = location.search.split('utm_skyhub=')[1]
    if(skParam != undefined) {
        asyncStack.push(['_setCustomVar', 5, "utm_skyhub", skParam.split('&')[0], 2]);
    }
}

