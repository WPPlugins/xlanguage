<?php echo '<?xml version="1.0" encoding="utf-8" ?>' ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" encoding="utf-8" omit-xml-declaration="yes" standalone="yes" doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" cdata-section-elements="script pre style" indent="yes" media-type="text/html" />
    <xsl:template match="/ParserLog">
        <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
            <link rel="stylesheet" type="text/css" href="<?php echo get_settings('siteurl'); ?>/wp-admin/wp-admin.css" />
            </head>
            <body>
                <xsl:choose>
                    <xsl:when test="count(descendant::log) = 0">
                        <?php _e('There was no XHTML parsing error.', 'xlanguage'); ?>
                    </xsl:when>
                    <xsl:otherwise>
                        <table border="0" class="widefat">
                            <thead>
                            <tr>
                                <th style="text-align: left"><?php _e('Time', 'xlanguage') ?></th>
                                <th style="text-align: left"><?php _e('Language', 'xlanguage') ?></th>
                                <th style="text-align: left"><?php _e('URL', 'xlanguage') ?></th>
                                <th style="text-align: left"><?php _e('Error', 'xlanguage') ?></th>
                                <th style="text-align: left"><?php _e('Content', 'xlanguage') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                                    <xsl:apply-templates select="log" />
                            </tbody>
                        </table>
                    </xsl:otherwise>
                </xsl:choose>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="log">
        <tr>
            <xsl:if test="position() mod 2 = 0">
                <xsl:attribute name="class">alternate</xsl:attribute>
            </xsl:if>
            <td><xsl:value-of select="time" /></td>
            <td><xsl:value-of select="lang" /></td>
            <td><a><xsl:attribute name="href"><xsl:value-of select="request" /></xsl:attribute><xsl:value-of select="request" /></a></td>
            <td><xsl:value-of select="error" /></td>
            <td><xsl:value-of select="precontent" /><span style="color: gray"><xsl:value-of select="postcontent" /></span></td>
        </tr>
    </xsl:template>
</xsl:stylesheet>
