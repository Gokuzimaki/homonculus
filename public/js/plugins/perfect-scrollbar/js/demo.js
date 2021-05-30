function changeSize() {
    var width = parseInt($("#Width").val());
    var height = parseInt($("#Height").val());

    $("#Demo").width(width).height(height);

    // update scrollbars
    $('#Demo').perfectScrollbar('update');

    // or even with vanilla JS!
    Ps.update(document.getElementById('Demo'));
}

$(function() {
    $('#Demo').perfectScrollbar();

    // with vanilla JS!
    Ps.initialize(document.getElementById('Demo'));
});