function newscreen(title,location)
{
    breedte = screen.width * 0.9;
    hoogte = screen.height * 0.9;
    mywindow = window.open(location,'MyWindow', 'width=' + breedte + ',height=' + hoogte + ',resizable=yes,status=no,toolbar=no,menubar=no,location=no');
    mywindow.focus();
}

