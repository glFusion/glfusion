vimeo_Reg = /https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/;
function VimeoGetID(e) {
  var t = "";
  var match = e.match(vimeo_Reg);
  if (match){
    return match[3];
  } else {
    return match;
  }
}(function() {
    CKEDITOR.plugins.add("vimeo", {
        lang: ["en"],
        init: function(e) {
            e.addCommand("vimeo", new CKEDITOR.dialogCommand("vimeo", {
                allowedContent: "iframe[!width,!height,!src,!frameborder,!allowfullscreen]; object param[*]"
            }));
            e.ui.addButton("Vimeo", {
                label: e.lang.vimeo.button,
                toolbar: "insert",
                command: "vimeo",
                icon: this.path + "images/icon.png"
            });
            CKEDITOR.dialog.add("vimeo", function(t) {
                var n;
                return {
                    title: e.lang.vimeo.title,
                    minWidth: 500,
                    minHeight: 100,
                    contents: [{
                        id: "vimeoPlugin",
                        expand: true,
                        elements: [{
                            id: "txtEmbed",
                            type: "textarea",
                            label: e.lang.vimeo.txtEmbed,
                            autofocus: "autofocus",
                            validate: function() {
                                if (this.isEnabled()) {
                                    if (!this.getValue()) {
                                        alert(e.lang.vimeo.noCode);
                                        return false
                                    } else if (this.getValue().length === 0 || this.getValue().indexOf("//") === -1) {
                                        alert(e.lang.vimeo.invalidEmbed);
                                        return false
                                    }
                                }
                            }
                        }, {
                            type: "hbox",
                            widths: ["15%", "15%", "15%", "15%", "15%"],
                            children: [{
                                type: "text",
                                id: "txtWidth",
                                width: "60px",
                                label: e.lang.vimeo.txtWidth,
                                "default": e.config.vimeo_width != null ? e.config.vimeo_width : "560",
                                validate: function() {
                                    if (this.getValue()) {
                                        var t = parseInt(this.getValue()) || 0;
                                        if (t === 0) {
                                            alert(e.lang.vimeo.invalidWidth);
                                            return false
                                        }
                                    } else {
                                        alert(e.lang.vimeo.noWidth);
                                        return false
                                    }
                                }
                            }, {
                                type: "text",
                                id: "txtHeight",
                                width: "60px",
                                label: e.lang.vimeo.txtHeight,
                                "default": e.config.vimeo_height != null ? e.config.vimeo_height : "315",
                                validate: function() {
                                    if (this.getValue()) {
                                        var t = parseInt(this.getValue()) || 0;
                                        if (t === 0) {
                                            alert(e.lang.vimeo.invalidHeight);
                                            return false
                                        }
                                    } else {
                                        alert(e.lang.vimeo.noHeight);
                                        return false
                                    }
                                }
                            }, {
                                id: "txtAlign",
                                type: "select",
                                label: e.lang.vimeo.txtAlign,
                                items: [
                                    [e.lang.vimeo.left, "left"],
                                    [e.lang.vimeo.right, "right"],
                                    [e.lang.vimeo.center, "center"]
                                ]
                            }, {
                                id: "txtPad",
                                type: "text",
                                width: "60px",
                                "default": "0",
                                label: e.lang.vimeo.txtPad
                            }, {
                                id: "txtResponsive",
                                type: "select",
                                width: "60px",
                                label: e.lang.vimeo.txtResponsive,
                                items: [
                                    [e.lang.vimeo.no, "0"],
                                    [e.lang.vimeo.yes, "1"]
                                ]
                            }]
                        }]
                    }],
                    onOk: function() {
                        var e = "";
                        var t = "";
                        var n = this.getValueOf("vimeoPlugin", "txtWidth");
                        var r = this.getValueOf("vimeoPlugin", "txtHeight");
                        var i = this.getValueOf("vimeoPlugin", "txtEmbed");
                        var s = this.getValueOf("vimeoPlugin", "txtPad");
                        var p = this.getValueOf("vimeoPlugin", "txtResponsive");
                        var o = VimeoGetID(i);
                        t += " width:" + n;
                        t += " height:" + r;
                        t += " responsive:" + p;
                        alignment = this.getContentElement("vimeoPlugin", "txtAlign").getValue();
                        if (alignment) {
                            t += " align:" + alignment
                        }
                        if (s > 0) {
                            t += " pad:" + s
                        }
                        e += "[vimeo:" + o + " " + t + "]";
                        var u = this.getParentEditor();
                        u.insertHtml(e)
                    }
                }
            })
        }
    })
})()