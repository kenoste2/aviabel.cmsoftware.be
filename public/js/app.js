function newscreen(title,location)
{
    breedte = screen.width * 0.9;
    hoogte = screen.height * 0.9;
    mywindow = window.open(location,'_blank', 'width=' + breedte + ',height=' + hoogte + '');
    mywindow.focus();
}

