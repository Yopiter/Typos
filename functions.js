function setApply(iID, bApply) {
    var xHttp = new XMLHttpRequest();
    xHttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById(iID).innerHTML = this.responseText;
        }
    };
    var apply = bApply ? 1 : 0;
    xHttp.open("POST", "Ajax.php", true);
    xHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xHttp.send("action=apply&apply=" + apply + "&ID=" + iID);
}
