				<form id="register-form">
					<div id="preloader">
						<div class="overlay"></div>
						<div class="sk-fading-circle">
							<div class="sk-circle1 sk-circle"></div>
							<div class="sk-circle2 sk-circle"></div>
							<div class="sk-circle3 sk-circle"></div>
							<div class="sk-circle4 sk-circle"></div>
							<div class="sk-circle5 sk-circle"></div>
							<div class="sk-circle6 sk-circle"></div>
							<div class="sk-circle7 sk-circle"></div>
							<div class="sk-circle8 sk-circle"></div>
							<div class="sk-circle9 sk-circle"></div>
							<div class="sk-circle10 sk-circle"></div>
							<div class="sk-circle11 sk-circle"></div>
							<div class="sk-circle12 sk-circle"></div>
						</div>
					</div>
					<input type="hidden" name="action" value="ajaxregister">
					<input type="hidden" name="security" value="<?php echo wp_create_nonce( "registration_nonce" ); ?>">
					<div class="container">
						<div>
							<div id="globalErrors">
								<span class="help-block"></span>
							</div>
						</div>
						<?php if ( get_option( $sha_ureg_prefix . 'allow_username' ) == 1 ): ?>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group" id="regUserGroup">
									<label for="regUserName"><?php _e( 'Username', 'sha-ureg' ); ?></label>
									<input type="text" class="form-control" name="login" id="regUser" placeholder="<?php _e( 'Choose your username', 'sha-ureg' ); ?>" />
									<span class="help-block"></span>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group" id="regEmailGroup">
									<label for="regEmail"><?php _e( 'Email', 'sha-ureg' ); ?></label>
									<input type="text" class="form-control" name="email" id="regEmail" placeholder="<?php _e( 'Choose your email', 'sha-ureg' ); ?>" />
									<span class="help-block"></span>
								</div>
							</div>
						</div>
						<?php if ( get_option( $sha_ureg_prefix . 'allow_password' ) == 1 ): ?>
						<div class="row">
							<div class="col-md-12">
								<div class="form-group" id="regPassGroup">
									<label for="regPassword"><?php _e( 'Password', 'sha-ureg' ); ?></label>
									<input type="password" class="form-control" name="pass" id="regPass" />
									<span class="help-block"></span>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( get_option( $sha_ureg_prefix . 'use_recaptcha' ) == 1 ): ?>
						<div class="row">
							<div class="col-md-12 text-center">
								<div class="form-group" id="regCaptchaGroup" style="margin: 0 auto">
									<div class="captcha g-recaptcha" data-sitekey="<?php echo get_option( $sha_ureg_prefix . 'rec_site_key' ); ?>"></div>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<?php if ( get_option( $sha_ureg_prefix . 'need_agreement' ) == 1 ): ?>
						<div class="row">
							<div class="col-md-12">
								<div class="checkbox" id="regAgreement">
									<label>
										<input type="checkbox" id="policy"> <?php echo get_option( $sha_ureg_prefix . 'agreement_label' ); ?>
									</label>
								</div>
							</div>
						</div>
                       <?php endif; ?>
                       <div class="row">
							<div class="col-md-12 text-center">
								<button class="btn btn-default" type="submit"><?php _e( 'Register', 'sha-ureg' ); ?></button>
							</div>
                       </div>
				   </div>
               </form>
